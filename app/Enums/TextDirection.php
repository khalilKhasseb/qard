<?php

namespace App\Enums;

/**
 * Text direction for languages.
 * Must match: resources/js/types/contracts/enums.ts > TextDirection
 */
enum TextDirection: string
{
    case Ltr = 'ltr';
    case Rtl = 'rtl';

    public function label(): string
    {
        return match ($this) {
            self::Ltr => 'Left to Right',
            self::Rtl => 'Right to Left',
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
