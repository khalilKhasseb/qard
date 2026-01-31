<?php

namespace App\Enums;

/**
 * Translation verification statuses.
 * Must match: resources/js/types/contracts/enums.ts > TranslationVerificationStatus
 */
enum TranslationVerificationStatus: string
{
    case Pending = 'pending';
    case AutoVerified = 'auto_verified';
    case Approved = 'approved';
    case NeedsReview = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::AutoVerified => 'Auto Verified',
            self::Approved => 'Approved',
            self::NeedsReview => 'Needs Review',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::AutoVerified => 'info',
            self::Approved => 'success',
            self::NeedsReview => 'danger',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
