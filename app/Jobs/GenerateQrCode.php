<?php

namespace App\Jobs;

use App\Models\BusinessCard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateQrCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries = 3;

    public function __construct(
        public BusinessCard $card
    ) {}

    public function handle(): void
    {
        $url = $this->card->full_url;

        // Using chillerlan/php-qrcode which was installed with Filament
        $options = new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
            'scale' => 10,
            'quietzoneSize' => 2,
        ]);

        $qrcode = new \chillerlan\QRCode\QRCode($options);
        $imageData = $qrcode->render($url);

        $filename = "users/{$this->card->user_id}/cards/{$this->card->id}/qr/qrcode_".time().'.png';
        Storage::disk('public')->put($filename, $imageData, 'public');

        $this->card->update([
            'qr_code_url' => Storage::disk('public')->url($filename),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('GenerateQrCode failed', [
            'card_id' => $this->card->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
