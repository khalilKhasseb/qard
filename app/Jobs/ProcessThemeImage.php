<?php

namespace App\Jobs;

use App\Models\Theme;
use App\Models\ThemeImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessThemeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    public function __construct(
        public ThemeImage $themeImage,
        public ?Theme $theme = null
    ) {}

    public function handle(): void
    {
        $sourcePath = $this->themeImage->file_path;

        if (!Storage::disk('public')->exists($sourcePath)) {
            return;
        }

        $imageContent = Storage::disk('public')->get($sourcePath);
        $imageInfo = @getimagesizefromstring($imageContent);

        if (!$imageInfo) {
            return;
        }

        [$width, $height] = $imageInfo;

        $this->themeImage->update([
            'width' => $width,
            'height' => $height,
        ]);

        if ($this->theme) {
            $config = $this->theme->config;
            $config['images'][$this->themeImage->file_type] = [
                'url' => $this->themeImage->url,
                'width' => $width,
                'height' => $height,
            ];
            $this->theme->update(['config' => $config]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('ProcessThemeImage failed', [
            'theme_image_id' => $this->themeImage->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
