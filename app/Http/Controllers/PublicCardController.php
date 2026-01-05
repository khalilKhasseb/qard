<?php

namespace App\Http\Controllers;

use App\Models\BusinessCard;
use App\Services\CardService;
use Illuminate\Http\Request;

class PublicCardController extends Controller
{
    public function __construct(
        protected CardService $cardService
    ) {}

    public function bySlug(string $slug)
    {
        $card = $this->cardService->getCardBySlug($slug);

        if (!$card) {
            abort(404);
        }

        $this->cardService->trackView($card, $this->getTrackingData(request()));

        return view('cards.public', [
            'card' => $card,
            'theme' => $card->theme,
            'sections' => $card->activeSections,
        ]);
    }

    public function byShareUrl(string $shareUrl)
    {
        $card = $this->cardService->getCardByShareUrl($shareUrl);

        if (!$card) {
            abort(404);
        }

        $this->cardService->trackView($card, $this->getTrackingData(request()));

        return view('cards.public', [
            'card' => $card,
            'theme' => $card->theme,
            'sections' => $card->activeSections,
        ]);
    }

    public function byNfc(string $nfcId)
    {
        $card = $this->cardService->getCardByNfc($nfcId);

        if (!$card) {
            abort(404);
        }

        $this->cardService->trackNfcTap($card, $this->getTrackingData(request()));

        return view('cards.public', [
            'card' => $card,
            'theme' => $card->theme,
            'sections' => $card->activeSections,
        ]);
    }

    public function qrScan(string $shareUrl)
    {
        $card = $this->cardService->getCardByShareUrl($shareUrl);

        if (!$card) {
            abort(404);
        }

        $this->cardService->trackQrScan($card, $this->getTrackingData(request()));

        return redirect()->route('card.public.share', $shareUrl);
    }

    protected function getTrackingData(Request $request): array
    {
        return [
            'referrer' => $request->headers->get('referer'),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ];
    }
}
