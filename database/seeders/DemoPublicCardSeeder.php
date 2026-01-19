<?php

namespace Database\Seeders;

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\Language;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoPublicCardSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@qard.test'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
            ]
        );

        $language = Language::query()->where('code', 'en')->first();

        $theme = Theme::query()->where('is_system_default', true)->first();

        $card = BusinessCard::query()->updateOrCreate(
            ['user_id' => $user->id, 'custom_slug' => 'demo-public-card'],
            [
                'language_id' => $language?->id,
                'title' => [
                    'en' => 'Muayad Jaber',
                    'ar' => 'مؤيد جبر',
                ],
                'subtitle' => [
                    'en' => "DDS / Jordan University\nImplant Dentistry / State University Of New York",
                    'ar' => "دكتوراه في طب الأسنان / جامعة الأردن\nطب زراعة الأسنان / جامعة ولاية نيويورك",
                ],
                'cover_image_path' => 'https://placehold.co/1200x600/png?text=Cover',
                'profile_image_path' => 'https://placehold.co/400x400/png?text=Avatar',
                'theme_id' => $theme?->id,
                'share_url' => Str::random(10),
                'qr_code_url' => null,
                'nfc_identifier' => null,
                'is_published' => true,
                'is_primary' => true,
            ]
        );

        CardSection::query()->where('business_card_id', $card->id)->delete();

        $sections = [
            [
                'section_type' => 'social',
                'title' => 'Social',
                'content' => [
                    'en' => [
                        'location' => 'https://maps.google.com/?q=Ramallah',
                        'facebook' => 'https://facebook.com/',
                        'instagram' => 'https://instagram.com/',
                        'whatsapp' => 'https://wa.me/970592987331',
                    ],
                    'ar' => [
                        'location' => 'https://maps.google.com/?q=Ramallah',
                        'facebook' => 'https://facebook.com/',
                        'instagram' => 'https://instagram.com/',
                        'whatsapp' => 'https://wa.me/970592987331',
                    ],
                ],
                'sort_order' => 0,
            ],
            [
                'section_type' => 'contact',
                'title' => 'Contact',
                'content' => [
                    'en' => [
                        'email' => 'drmuayadclinic@gmail.com',
                        'phone' => '+970592987331',
                        'telephone' => '+97022987331',
                        'address' => 'Ramallah-Al-Ahleyya St.- Sham Center-6th floor',
                    ],
                    'ar' => [
                        'email' => 'drmuayadclinic@gmail.com',
                        'phone' => '+970592987331',
                        'telephone' => '+97022987331',
                        'address' => 'رام الله - شارع الأهلية - مركز الشام - الطابق السادس',
                    ],
                ],
                'sort_order' => 1,
            ],
            [
                'section_type' => 'hours',
                'title' => 'Business Hours',
                'content' => [
                    'en' => [
                        'Monday' => '09:00 AM - 06:00 PM',
                        'Tuesday' => '09:00 AM - 06:00 PM',
                        'Wednesday' => '09:00 AM - 06:00 PM',
                        'Thursday' => '09:00 AM - 06:00 PM',
                        'Friday' => 'Closed',
                        'Saturday' => '09:00 AM - 06:00 PM',
                        'Sunday' => '09:00 AM - 06:00 PM',
                    ],
                    'ar' => [
                        'الإثنين' => '09:00 صباحاً - 06:00 مساءاً',
                        'الثلاثاء' => '09:00 صباحاً - 06:00 مساءاً',
                        'الأربعاء' => '09:00 صباحاً - 06:00 مساءاً',
                        'الخميس' => '09:00 صباحاً - 06:00 مساءاً',
                        'الجمعة' => 'مغلق',
                        'السبت' => '09:00 صباحاً - 06:00 مساءاً',
                        'الأحد' => '09:00 صباحاً - 06:00 مساءاً',
                    ],
                ],
                'sort_order' => 2,
            ],
            [
                'section_type' => 'gallery',
                'title' => 'Gallery',
                'content' => [
                    'en' => [
                        ['url' => 'https://placehold.co/600x600/png?text=Gallery+1', 'caption' => 'Gallery 1'],
                        ['url' => 'https://placehold.co/600x600/png?text=Gallery+2', 'caption' => 'Gallery 2'],
                    ],
                    'ar' => [
                        ['url' => 'https://placehold.co/600x600/png?text=Gallery+1', 'caption' => 'صورة 1'],
                        ['url' => 'https://placehold.co/600x600/png?text=Gallery+2', 'caption' => 'صورة 2'],
                    ],
                ],
                'sort_order' => 3,
            ],
            [
                'section_type' => 'about',
                'title' => 'About',
                'content' => [
                    'en' => "Specialized in implant dentistry and cosmetic procedures.\n\nThis is demo content.",
                    'ar' => "متخصص في طب زراعة الأسنان والتجميل.\n\nهذا محتوى تجريبي.",
                ],
                'sort_order' => 4,
            ],
            [
                'section_type' => 'services',
                'title' => 'Services',
                'content' => [
                    'en' => [
                        [
                            'name' => 'Dental Implants',
                            'description' => 'Implant placement and restoration.',
                            'price' => '$$',
                            'image_url' => 'https://placehold.co/400x400/png?text=Service',
                        ],
                        [
                            'name' => 'Teeth Whitening',
                            'description' => 'In-office whitening and follow-up kit.',
                            'price' => '$',
                            'image_url' => 'https://placehold.co/400x400/png?text=Service',
                        ],
                    ],
                    'ar' => [
                        [
                            'name' => 'زراعة الأسنان',
                            'description' => 'زراعة وتركيبات الأسنان.',
                            'price' => '$$',
                            'image_url' => 'https://placehold.co/400x400/png?text=Service',
                        ],
                        [
                            'name' => 'تبييض الأسنان',
                            'description' => 'تبييض بالعيادة مع مجموعة متابعة.',
                            'price' => '$',
                            'image_url' => 'https://placehold.co/400x400/png?text=Service',
                        ],
                    ],
                ],
                'sort_order' => 5,
            ],
            [
                'section_type' => 'products',
                'title' => 'Products',
                'content' => [
                    'en' => [
                        [
                            'name' => 'Whitening Kit',
                            'description' => 'Home whitening kit.',
                            'price' => '$',
                            'image_url' => 'https://placehold.co/400x400/png?text=Product',
                        ],
                    ],
                    'ar' => [
                        [
                            'name' => 'مجموعة تبييض',
                            'description' => 'مجموعة تبييض للاستخدام المنزلي.',
                            'price' => '$',
                            'image_url' => 'https://placehold.co/400x400/png?text=Product',
                        ],
                    ],
                ],
                'sort_order' => 6,
            ],
            [
                'section_type' => 'testimonials',
                'title' => 'Testimonials',
                'content' => [
                    'en' => [
                        [
                            'quote' => 'Great experience and very professional.',
                            'author' => 'Patient A',
                            'company' => '',
                        ],
                    ],
                    'ar' => [
                        [
                            'quote' => 'تجربة رائعة وتعامل احترافي.',
                            'author' => 'مراجع',
                            'company' => '',
                        ],
                    ],
                ],
                'sort_order' => 7,
            ],
            [
                'section_type' => 'appointments',
                'title' => 'Appointments',
                'content' => [
                    'en' => [
                        'instructions' => 'Book your appointment online.',
                        'booking_url' => 'https://example.com/book',
                    ],
                    'ar' => [
                        'instructions' => 'احجز موعدك عبر الإنترنت.',
                        'booking_url' => 'https://example.com/book',
                    ],
                ],
                'sort_order' => 8,
            ],
        ];

        foreach ($sections as $s) {
            CardSection::create([
                'business_card_id' => $card->id,
                'section_type' => $s['section_type'],
                'title' => $s['title'],
                'content' => $s['content'],
                'sort_order' => $s['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
