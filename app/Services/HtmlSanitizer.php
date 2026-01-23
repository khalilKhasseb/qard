<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags for user-generated content
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'a', 'img', 'blockquote', 'span', 'div',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /**
     * Allowed attributes for specific tags
     */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'span' => ['style'],
        'div' => ['style', 'class'],
        'p' => ['style'],
        'table' => ['class', 'style'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /**
     * Allowed CSS properties for inline styles
     */
    private const ALLOWED_CSS_PROPERTIES = [
        'color', 'background-color', 'font-size', 'font-weight', 'font-family',
        'text-align', 'margin', 'padding', 'border', 'border-radius',
        'width', 'height', 'display', 'line-height',
    ];

    /**
     * Sanitize HTML content
     */
    public function sanitize(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Load HTML into DOMDocument
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);

        // Wrap in UTF-8 declaration to handle encoding properly
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Process all nodes recursively
        $this->processNode($dom->documentElement);

        // Get the sanitized HTML
        $sanitized = $dom->saveHTML();

        // Remove the XML declaration we added
        $sanitized = preg_replace('/^<!DOCTYPE.+?>/', '', $sanitized);
        $sanitized = str_replace(['<?xml encoding="utf-8" ?>', '<html>', '</html>', '<body>', '</body>'], '', $sanitized);

        return trim($sanitized);
    }

    /**
     * Process a DOM node recursively
     */
    private function processNode(\DOMNode $node): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->nodeName);

            // If tag is not allowed, remove it but keep its children
            if (! in_array($tagName, self::ALLOWED_TAGS)) {
                $this->replaceWithChildren($node);

                return;
            }

            // Process attributes
            $this->processAttributes($node, $tagName);

            // Special handling for links
            if ($tagName === 'a') {
                $this->processLink($node);
            }

            // Special handling for images
            if ($tagName === 'img') {
                $this->processImage($node);
            }
        }

        // Process child nodes
        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                $this->processNode($child);
            }
        }
    }

    /**
     * Process node attributes
     */
    private function processAttributes(\DOMNode $node, string $tagName): void
    {
        $allowedAttrs = self::ALLOWED_ATTRIBUTES[$tagName] ?? [];

        if ($node->attributes === null) {
            return;
        }

        $attrsToRemove = [];
        foreach ($node->attributes as $attr) {
            $attrName = strtolower($attr->name);

            // Remove dangerous event handlers
            if (preg_match('/^on/i', $attrName)) {
                $attrsToRemove[] = $attrName;

                continue;
            }

            // Check if attribute is allowed for this tag
            if (! in_array($attrName, $allowedAttrs)) {
                $attrsToRemove[] = $attrName;

                continue;
            }

            // Sanitize attribute values
            $value = $attr->value;

            // Remove javascript: and data: URLs
            if (in_array($attrName, ['href', 'src'])) {
                if (preg_match('/^(javascript|data|vbscript):/i', $value)) {
                    $attrsToRemove[] = $attrName;

                    continue;
                }
            }

            // Sanitize style attribute
            if ($attrName === 'style') {
                $sanitizedStyle = $this->sanitizeStyle($value);
                if (empty($sanitizedStyle)) {
                    $attrsToRemove[] = $attrName;
                } else {
                    $node->setAttribute($attrName, $sanitizedStyle);
                }
            }
        }

        // Remove disallowed attributes
        foreach ($attrsToRemove as $attrName) {
            $node->removeAttribute($attrName);
        }
    }

    /**
     * Sanitize inline CSS styles
     */
    private function sanitizeStyle(string $style): string
    {
        $sanitized = [];
        $declarations = explode(';', $style);

        foreach ($declarations as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = trim(strtolower($parts[0]));
            $value = trim($parts[1]);

            // Skip dangerous patterns
            if (preg_match('/(javascript|expression|behavior|binding)/i', $value)) {
                continue;
            }

            // Only allow whitelisted properties
            if (in_array($property, self::ALLOWED_CSS_PROPERTIES)) {
                $sanitized[] = "$property: $value";
            }
        }

        return implode('; ', $sanitized);
    }

    /**
     * Process link nodes
     */
    private function processLink(\DOMNode $node): void
    {
        if ($node->hasAttribute('href')) {
            $href = $node->getAttribute('href');

            // Ensure external links open in new tab and have security attributes
            if (preg_match('/^https?:\/\//i', $href)) {
                $node->setAttribute('target', '_blank');
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    /**
     * Process image nodes
     */
    private function processImage(\DOMNode $node): void
    {
        if ($node->hasAttribute('src')) {
            $src = $node->getAttribute('src');

            // Only allow HTTPS URLs and data URIs for images
            if (! preg_match('/^(https:\/\/|data:image\/(png|jpg|jpeg|gif|webp|svg\+xml);base64,|\/)/i', $src)) {
                // Remove the entire image if src is not safe
                $node->parentNode->removeChild($node);
            }
        }
    }

    /**
     * Replace a node with its children
     */
    private function replaceWithChildren(\DOMNode $node): void
    {
        $parent = $node->parentNode;

        if ($parent === null) {
            return;
        }

        while ($node->firstChild) {
            $child = $node->firstChild;
            $parent->insertBefore($child, $node);
        }

        $parent->removeChild($node);
    }
}
