<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'name' => 'English',
                'code' => 'en',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'العربية',
                'code' => 'ar',
                'direction' => 'rtl',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'עברית',
                'code' => 'he',
                'direction' => 'rtl',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
