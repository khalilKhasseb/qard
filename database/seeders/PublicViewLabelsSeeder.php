<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class PublicViewLabelsSeeder extends Seeder
{
    public function run(): void
    {
        $labelDefaults = [
            'en' => [
                'available_in' => 'Available in',
                'powered_by' => 'Powered by',
                'download' => 'Download',
                'download_qr_png' => 'Download QR',
                'download_qr_svg' => 'Download SVG',
                'view_website' => 'View Website',
                'book_appointment' => 'Book Appointment',
                'am' => 'AM',
                'pm' => 'PM',
                'closed' => 'Closed',
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'ar' => [
                'available_in' => 'متوفر بـ',
                'powered_by' => 'مشغل بواسطة',
                'download' => 'تنزيل',
                'download_qr_png' => 'تحميل QR',
                'download_qr_svg' => 'تحميل SVG',
                'view_website' => 'عرض الموقع',
                'book_appointment' => 'حجز موعد',
                'am' => 'ص',
                'pm' => 'م',
                'closed' => 'مغلق',
                'monday' => 'الاثنين',
                'tuesday' => 'الثلاثاء',
                'wednesday' => 'الأربعاء',
                'thursday' => 'الخميس',
                'friday' => 'الجمعة',
                'saturday' => 'السبت',
                'sunday' => 'الأحد',
            ],
        ];

        foreach ($labelDefaults as $code => $labels) {
            $language = Language::where('code', $code)->first();
            if (! $language) {
                continue;
            }

            $current = is_array($language->labels) ? $language->labels : [];
            $language->update(['labels' => array_merge($current, $labels)]);
        }
    }
}
