<?php

namespace App\Enums;

/**
 * Subscription statuses.
 * Must match: resources/js/types/contracts/enums.ts > SubscriptionStatus
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case Expired = 'expired';
    case Trial = 'trial';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Canceled => 'Canceled',
            self::Expired => 'Expired',
            self::Trial => 'Trial',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Canceled => 'warning',
            self::Expired => 'danger',
            self::Trial => 'info',
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
