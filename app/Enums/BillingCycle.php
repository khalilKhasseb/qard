<?php

namespace App\Enums;

/**
 * Subscription billing cycles.
 * Must match: resources/js/types/contracts/enums.ts > BillingCycle
 */
enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Lifetime = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
            self::Lifetime => 'Lifetime',
        };
    }

    public function intervalDays(): ?int
    {
        return match ($this) {
            self::Monthly => 30,
            self::Yearly => 365,
            self::Lifetime => null,
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
