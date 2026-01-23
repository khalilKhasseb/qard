<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ThemeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme_id',
        'file_path',
        'file_type',
        'width',
        'height',
        'file_size',
        'mime_type',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
        ];
    }

    public const FILE_TYPES = [
        'background',
        'header',
        'logo',
        'favicon',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getFileSizeForHumansAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function getDimensionsAttribute(): string
    {
        if ($this->width && $this->height) {
            return "{$this->width}x{$this->height}";
        }

        return 'Unknown';
    }

    public function delete(): bool
    {
        Storage::disk('public')->delete($this->file_path);

        return parent::delete();
    }
}
