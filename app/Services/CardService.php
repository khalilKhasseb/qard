<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CardService
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function createCard(User $user, array $data, ?Template $template = null): BusinessCard
    {
        if (!$user->canCreateCard()) {
            throw new \Exception('Card limit reached for your plan');
        }

        $card = BusinessCard::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'template_id' => $template?->id,
            'theme_id' => $data['theme_id'] ?? null,
            'custom_slug' => $data['custom_slug'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'is_primary' => $user->cards()->count() === 0,
        ]);

        if ($template && !empty($template->default_sections)) {
            $this->createDefaultSections($card, $template->default_sections);
        }

        return $card;
    }

    public function updateCard(BusinessCard $card, array $data): BusinessCard
    {
        $card->update($data);
        return $card->fresh();
    }

    public function duplicateCard(BusinessCard $card, ?User $user = null, ?string $newTitle = null): BusinessCard
    {
        $targetUser = $user ?? $card->user;

        if (!$targetUser->canCreateCard()) {
            throw new \Exception('Card limit reached for your plan');
        }

        $newCard = $card->replicate(['share_url', 'qr_code_url', 'nfc_identifier', 'views_count', 'shares_count']);
        $newCard->user_id = $targetUser->id;
        $newCard->title = $newTitle ?? $card->title . ' (Copy)';
        $newCard->share_url = Str::random(10);
        $newCard->is_published = false;
        $newCard->is_primary = false;
        $newCard->save();

        foreach ($card->sections as $section) {
            $newSection = $section->replicate();
            $newSection->business_card_id = $newCard->id;
            $newSection->save();
        }

        return $newCard;
    }

    public function deleteCard(BusinessCard $card): bool
    {
        return $card->delete();
    }

    protected function createDefaultSections(BusinessCard $card, array $sections): void
    {
        foreach ($sections as $index => $sectionData) {
            CardSection::create([
                'business_card_id' => $card->id,
                'section_type' => $sectionData['type'],
                'title' => $sectionData['title'],
                'content' => $sectionData['content'] ?? [],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    public function addSection(BusinessCard $card, array $data): CardSection
    {
        $maxOrder = $card->sections()->max('sort_order') ?? -1;

        return CardSection::create([
            'business_card_id' => $card->id,
            'section_type' => $data['section_type'],
            'title' => $data['title'],
            'content' => $data['content'] ?? [],
            'sort_order' => $maxOrder + 1,
            'is_active' => $data['is_active'] ?? true,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function updateSection(CardSection $section, array $data): CardSection
    {
        if (isset($data['image_path'])) {
            $section->image_path = $data['image_path'];
        }
        
        $section->update($data);
        return $section->fresh();
    }

    public function reorderSections(BusinessCard $card, array $sectionIds): void
    {
        foreach ($sectionIds as $order => $sectionId) {
            CardSection::where('id', $sectionId)
                ->where('business_card_id', $card->id)
                ->update(['sort_order' => $order]);
        }
    }

    public function deleteSection(CardSection $section): bool
    {
        return $section->delete();
    }

    public function generateQrCode(BusinessCard $card): string
    {
        $url = $card->full_url;

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($url);

        $filename = "qrcodes/{$card->id}_" . time() . '.png';
        \Storage::disk('public')->put($filename, $qrCode);

        $card->update(['qr_code_url' => \Storage::disk('public')->url($filename)]);

        return $card->qr_code_url;
    }

    public function assignNfcIdentifier(BusinessCard $card, string $nfcId): void
    {
        $existing = BusinessCard::where('nfc_identifier', $nfcId)->first();
        if ($existing && $existing->id !== $card->id) {
            throw new \Exception('NFC identifier already in use');
        }

        $card->update(['nfc_identifier' => $nfcId]);
    }

    public function setCustomSlug(BusinessCard $card, string $slug): void
    {
        $slug = Str::slug($slug);

        $existing = BusinessCard::where('custom_slug', $slug)->first();
        if ($existing && $existing->id !== $card->id) {
            throw new \Exception('This URL is already taken');
        }

        $card->update(['custom_slug' => $slug]);
    }

    public function trackView(BusinessCard $card, array $data = []): void
    {
        $card->incrementViews();

        AnalyticsEvent::track($card->id, 'view', null, $data);
    }

    public function trackNfcTap(BusinessCard $card, array $data = []): void
    {
        $card->incrementViews();

        AnalyticsEvent::track($card->id, 'nfc_tap', null, $data);
    }

    public function trackQrScan(BusinessCard $card, array $data = []): void
    {
        $card->incrementViews();

        AnalyticsEvent::track($card->id, 'qr_scan', null, $data);
    }

    public function trackSectionClick(BusinessCard $card, CardSection $section, array $data = []): void
    {
        AnalyticsEvent::track($card->id, 'section_click', $section->id, $data);
    }

    public function trackShare(BusinessCard $card, string $platform, array $data = []): void
    {
        $card->incrementShares();

        AnalyticsEvent::track($card->id, 'social_share', null, array_merge($data, [
            'metadata' => ['platform' => $platform],
        ]));
    }

    public function getCardBySlug(string $slug): ?BusinessCard
    {
        return BusinessCard::where('custom_slug', $slug)
            ->published()
            ->with(['sections' => fn($q) => $q->active()->ordered(), 'theme'])
            ->first();
    }

    public function getCardByShareUrl(string $shareUrl): ?BusinessCard
    {
        return BusinessCard::where('share_url', $shareUrl)
            ->published()
            ->with(['sections' => fn($q) => $q->active()->ordered(), 'theme'])
            ->first();
    }

    public function getCardByNfc(string $nfcId): ?BusinessCard
    {
        return BusinessCard::where('nfc_identifier', $nfcId)
            ->published()
            ->with(['sections' => fn($q) => $q->active()->ordered(), 'theme'])
            ->first();
    }

    public function getAnalytics(BusinessCard $card, string $period = 'month'): array
    {
        return [
            'stats' => $this->analyticsService->getCardStats($card, $period),
            'views_over_time' => $this->analyticsService->getViewsOverTime($card, $period),
            'top_referrers' => $this->analyticsService->getTopReferrers($card, $period),
            'device_breakdown' => $this->analyticsService->getDeviceBreakdown($card, $period),
            'browser_breakdown' => $this->analyticsService->getBrowserBreakdown($card, $period),
            'country_breakdown' => $this->analyticsService->getCountryBreakdown($card, $period),
            'section_clicks' => $this->analyticsService->getSectionClickStats($card, $period),
            'event_types' => $this->analyticsService->getEventTypeBreakdown($card, $period),
            'recent_events' => $this->analyticsService->getRecentEvents($card, 20),
        ];
    }
}
