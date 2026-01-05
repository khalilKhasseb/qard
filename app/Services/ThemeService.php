<?php

namespace App\Services;

use App\Models\BusinessCard;
use App\Models\Theme;
use App\Models\ThemeImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ThemeService
{
    public function __construct(
        private CssSanitizer $cssSanitizer
    ) {}
    public function createTheme(User $user, array $data): Theme
    {
        if (!$user->canCreateTheme()) {
            throw new \Exception('Theme limit reached for your plan');
        }

        $config = array_replace_recursive(
            Theme::getDefaultConfig(),
            $data['config'] ?? []
        );

        // Sanitize custom CSS to prevent XSS
        $config = $this->cssSanitizer->sanitizeThemeConfig($config);

        return Theme::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'config' => $config,
            'is_public' => $data['is_public'] ?? false,
        ]);
    }

    public function updateTheme(Theme $theme, array $data): Theme
    {
        if (isset($data['config'])) {
            $data['config'] = array_replace_recursive(
                $theme->config,
                $data['config']
            );
            
            // Sanitize custom CSS to prevent XSS
            $data['config'] = $this->cssSanitizer->sanitizeThemeConfig($data['config']);
        }

        $theme->update($data);
        return $theme->fresh();
    }

    public function duplicateTheme(Theme $theme, User $user, ?string $newName = null): Theme
    {
        if (!$user->canCreateTheme()) {
            throw new \Exception('Theme limit reached for your plan');
        }

        return Theme::create([
            'user_id' => $user->id,
            'name' => $newName ?? $theme->name . ' (Copy)',
            'config' => $theme->config,
            'is_public' => false,
        ]);
    }

    public function processImage(
        UploadedFile $file,
        string $type,
        User $user,
        ?Theme $theme = null
    ): ThemeImage {
        $allowedTypes = ThemeImage::FILE_TYPES;
        if (!in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid image type');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File too large. Max 5MB');
        }

        $directory = "themes/{$user->id}";
        $filename = uniqid($type . '_') . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        $imageInfo = @getimagesize($file->getRealPath());

        $themeImage = ThemeImage::create([
            'user_id' => $user->id,
            'theme_id' => $theme?->id,
            'file_path' => $path,
            'file_type' => $type,
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        if ($theme) {
            $this->updateThemeImageConfig($theme, $type, $themeImage);
        }

        return $themeImage;
    }

    protected function updateThemeImageConfig(Theme $theme, string $type, ThemeImage $image): void
    {
        $config = $theme->config;
        $config['images'][$type] = [
            'url' => $image->url,
            'width' => $image->width,
            'height' => $image->height,
        ];
        $theme->update(['config' => $config]);
    }

    public function generateCSS(Theme $theme): string
    {
        $config = $theme->config;

        $css = ":root {\n";
        foreach ($config['colors'] ?? [] as $key => $value) {
            $cssKey = str_replace('_', '-', $key);
            $css .= "  --{$cssKey}: {$value};\n";
        }
        $css .= "}\n\n";

        $css .= ".theme-wrapper {\n";
        $css .= "  background-color: var(--background);\n";
        $css .= "  font-family: {$config['fonts']['body']}, system-ui, sans-serif;\n";
        $css .= "  color: var(--text);\n";
        $css .= "  min-height: 100vh;\n";

        if (!empty($config['images']['background']['url'])) {
            $bg = $config['images']['background'];
            $css .= "  background-image: url({$bg['url']});\n";
            $css .= "  background-size: cover;\n";
            $css .= "  background-position: center;\n";
            $css .= "  background-attachment: fixed;\n";
        }
        $css .= "}\n\n";

        $css .= ".card-container {\n";
        $css .= "  background-color: var(--card-bg);\n";
        $css .= "  border-radius: {$config['layout']['border_radius']};\n";
        $css .= "  text-align: {$config['layout']['alignment']};\n";
        $css .= "  padding: 2rem;\n";

        $cardStyle = $config['layout']['card_style'] ?? 'elevated';
        if ($cardStyle === 'elevated') {
            $css .= "  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);\n";
        } elseif ($cardStyle === 'outlined') {
            $css .= "  border: 1px solid var(--border);\n";
        }
        $css .= "}\n\n";

        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "  font-family: {$config['fonts']['heading']}, system-ui, sans-serif;\n";
        $css .= "  color: var(--primary);\n";
        $css .= "}\n\n";

        $css .= ".btn-primary {\n";
        $css .= "  background-color: var(--primary);\n";
        $css .= "  color: #ffffff;\n";
        $css .= "  border-radius: 0.5rem;\n";
        $css .= "  padding: 0.75rem 1.5rem;\n";
        $css .= "  font-weight: 600;\n";
        $css .= "  transition: all 0.2s ease;\n";
        $css .= "}\n\n";

        $css .= ".btn-primary:hover {\n";
        $css .= "  background-color: var(--secondary);\n";
        $css .= "  transform: translateY(-2px);\n";
        $css .= "}\n\n";

        $css .= ".section {\n";
        $css .= "  padding: 1rem;\n";
        $css .= "  border-radius: 0.5rem;\n";
        $css .= "  background-color: rgba(255, 255, 255, 0.05);\n";
        $css .= "  margin-bottom: 1rem;\n";
        $css .= "}\n\n";

        if (!empty($config['custom_css'])) {
            $css .= "/* Custom CSS */\n{$config['custom_css']}\n";
        }

        return $css;
    }

    public function applyToCard(Theme $theme, BusinessCard $card): void
    {
        $oldTheme = $card->theme;

        $card->update([
            'theme_id' => $theme->id,
            'theme_overrides' => null,
        ]);

        $theme->incrementUsage();

        if ($oldTheme && $oldTheme->id !== $theme->id) {
            $oldTheme->decrementUsage();
        }
    }

    public function getAvailableThemes(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Theme::forUser($user->id)
            ->orderByDesc('is_system_default')
            ->orderByDesc('used_by_cards_count')
            ->get();
    }

    public function getPreviewHTML(Theme $theme): string
    {
        $css = $this->generateCSS($theme);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$theme->name} - Preview</title>
    <style>{$css}</style>
    <style>
        body { margin: 0; padding: 20px; }
        .preview-container { max-width: 640px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="theme-wrapper">
        <div class="preview-container">
            <div class="card-container">
                <h1>John Doe</h1>
                <p>Software Engineer</p>
                <div class="section">
                    <h3>Contact</h3>
                    <p>john@example.com</p>
                    <p>+1 234 567 890</p>
                </div>
                <div class="section">
                    <h3>Services</h3>
                    <ul>
                        <li>Web Development</li>
                        <li>Mobile Apps</li>
                        <li>Consulting</li>
                    </ul>
                </div>
                <button class="btn-primary">Contact Me</button>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
