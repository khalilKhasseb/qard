<?php

namespace App\Services;

class CssSanitizer
{
    /**
     * Dangerous patterns that could lead to XSS or other security issues
     */
    private const DANGEROUS_PATTERNS = [
        // JavaScript execution patterns
        '/javascript:/i',
        '/expression\s*\(/i',
        '/behavior\s*:/i',
        '/-moz-binding/i',
        '/vbscript:/i',
        '/data:text\/html/i',
        
        // Script tags and HTML
        '/<script[\s\S]*?<\/script>/i',
        '/<iframe[\s\S]*?<\/iframe>/i',
        '/<embed[\s\S]*?>/i',
        '/<object[\s\S]*?<\/object>/i',
        
        // Import from external sources (can be used to exfiltrate data)
        '/@import\s+["\']?(?!https?:\/\/(fonts\.googleapis\.com|fonts\.gstatic\.com))/i',
        
        // Event handlers
        '/on\w+\s*=/i',
        
        // Eval and other dangerous functions in CSS (modern browsers)
        '/calc\s*\([^)]*javascript/i',
    ];

    /**
     * Allowed CSS properties (whitelist approach)
     * This is a comprehensive list of safe CSS properties for styling
     */
    private const ALLOWED_PROPERTIES = [
        // Layout
        'display', 'position', 'top', 'right', 'bottom', 'left', 'float', 'clear',
        'z-index', 'overflow', 'overflow-x', 'overflow-y', 'clip', 'visibility',
        
        // Box model
        'width', 'height', 'max-width', 'max-height', 'min-width', 'min-height',
        'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        
        // Border
        'border', 'border-width', 'border-style', 'border-color', 'border-radius',
        'border-top', 'border-right', 'border-bottom', 'border-left',
        'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width',
        'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style',
        'border-top-color', 'border-right-color', 'border-bottom-color', 'border-left-color',
        'border-top-left-radius', 'border-top-right-radius', 'border-bottom-right-radius', 'border-bottom-left-radius',
        
        // Background
        'background', 'background-color', 'background-image', 'background-repeat',
        'background-position', 'background-size', 'background-attachment', 'background-clip',
        'background-origin',
        
        // Text
        'color', 'font', 'font-family', 'font-size', 'font-weight', 'font-style',
        'font-variant', 'line-height', 'letter-spacing', 'word-spacing', 'text-align',
        'text-decoration', 'text-indent', 'text-transform', 'text-shadow', 'white-space',
        'word-wrap', 'word-break', 'text-overflow', 'vertical-align',
        
        // Flexbox
        'flex', 'flex-direction', 'flex-wrap', 'flex-flow', 'justify-content',
        'align-items', 'align-content', 'order', 'flex-grow', 'flex-shrink', 'flex-basis',
        'align-self',
        
        // Grid
        'grid', 'grid-template-columns', 'grid-template-rows', 'grid-template-areas',
        'grid-column', 'grid-row', 'grid-area', 'grid-gap', 'gap', 'row-gap', 'column-gap',
        
        // Transform & Animation
        'transform', 'transform-origin', 'transition', 'animation', 'animation-name',
        'animation-duration', 'animation-timing-function', 'animation-delay',
        'animation-iteration-count', 'animation-direction', 'animation-fill-mode',
        'animation-play-state',
        
        // Effects
        'opacity', 'box-shadow', 'filter', 'backdrop-filter',
        
        // Other
        'cursor', 'outline', 'outline-width', 'outline-style', 'outline-color',
        'list-style', 'list-style-type', 'list-style-position', 'list-style-image',
        'table-layout', 'border-collapse', 'border-spacing', 'empty-cells', 'caption-side',
        'content', 'quotes', 'counter-reset', 'counter-increment',
        
        // Modern CSS
        'aspect-ratio', 'object-fit', 'object-position', 'resize', 'pointer-events',
        'user-select', 'box-sizing',
    ];

    /**
     * Sanitize CSS input to prevent XSS attacks
     */
    public function sanitize(string $css): string
    {
        if (empty($css)) {
            return '';
        }

        // Remove any dangerous patterns
        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            $css = preg_replace($pattern, '', $css);
        }

        // Remove HTML comments
        $css = preg_replace('/<!--.*?-->/s', '', $css);

        // Remove multi-line comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);

        // Parse and validate CSS properties
        $css = $this->validateCssProperties($css);

        // Ensure URLs are safe
        $css = $this->sanitizeUrls($css);

        return trim($css);
    }

    /**
     * Validate CSS properties against whitelist
     */
    private function validateCssProperties(string $css): string
    {
        // This is a simplified validation
        // For production, consider using a proper CSS parser
        
        $lines = explode("\n", $css);
        $sanitizedLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Keep selectors and structural elements
            if (empty($line) || 
                str_ends_with($line, '{') || 
                str_ends_with($line, '}') ||
                $line === '}' ||
                str_starts_with($line, '@media') ||
                str_starts_with($line, '@keyframes')) {
                $sanitizedLines[] = $line;
                continue;
            }

            // Check if line contains a property declaration
            if (strpos($line, ':') !== false) {
                [$property, $value] = array_map('trim', explode(':', $line, 2));
                $property = strtolower(trim($property));
                
                // Remove any trailing semicolon from property name
                $property = rtrim($property, ';');
                
                // Check if property is in whitelist
                if ($this->isPropertyAllowed($property)) {
                    // Additional validation on the value
                    if ($this->isValueSafe($value)) {
                        $sanitizedLines[] = $line;
                    }
                }
            }
        }

        return implode("\n", $sanitizedLines);
    }

    /**
     * Check if a CSS property is allowed
     */
    private function isPropertyAllowed(string $property): bool
    {
        $property = strtolower(trim($property));
        
        // Check exact match
        if (in_array($property, self::ALLOWED_PROPERTIES)) {
            return true;
        }
        
        // Check for vendor prefixes
        if (preg_match('/^(-webkit-|-moz-|-ms-|-o-)/', $property)) {
            $unprefixed = preg_replace('/^(-webkit-|-moz-|-ms-|-o-)/', '', $property);
            return in_array($unprefixed, self::ALLOWED_PROPERTIES);
        }
        
        return false;
    }

    /**
     * Check if a CSS value is safe
     */
    private function isValueSafe(string $value): bool
    {
        $value = strtolower($value);
        
        // Check for dangerous patterns in value
        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Sanitize URLs in CSS
     */
    private function sanitizeUrls(string $css): string
    {
        // Only allow HTTPS URLs and data URIs for images
        $css = preg_replace_callback(
            '/url\s*\(\s*["\']?(.*?)["\']?\s*\)/i',
            function ($matches) {
                $url = trim($matches[1], '\'" ');
                
                // Allow HTTPS URLs
                if (preg_match('/^https:\/\//i', $url)) {
                    return "url('{$url}')";
                }
                
                // Allow data URIs for images only
                if (preg_match('/^data:image\/(png|jpg|jpeg|gif|svg\+xml|webp);base64,/i', $url)) {
                    return "url('{$url}')";
                }
                
                // Allow relative URLs
                if (preg_match('/^\/[^\/]/', $url)) {
                    return "url('{$url}')";
                }
                
                // Remove anything else (javascript:, vbscript:, data:text/html, etc.)
                return '';
            },
            $css
        );

        return $css;
    }

    /**
     * Validate and sanitize CSS for theme configuration
     */
    public function sanitizeThemeConfig(array $config): array
    {
        if (isset($config['custom_css']) && is_string($config['custom_css'])) {
            $config['custom_css'] = $this->sanitize($config['custom_css']);
        }

        return $config;
    }
}
