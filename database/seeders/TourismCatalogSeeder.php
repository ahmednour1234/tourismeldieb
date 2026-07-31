<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TourismCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $admin = User::query()->firstOrNew(['email' => 'admin@hurgadaguide.example']);
        $admin->forceFill([
            'name' => 'Demo Admin',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
        ])->save();

        foreach ([
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'sort_order' => 1],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'sort_order' => 2],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'sort_order' => 3],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'direction' => 'ltr', 'sort_order' => 4],
        ] as $language) {
            DB::table('languages')->updateOrInsert(
                ['code' => $language['code']],
                $language + ['is_active' => true, 'updated_at' => $now, 'created_at' => $now],
            );
        }

        foreach ([
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_default' => true, 'sort_order' => 1],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_default' => false, 'sort_order' => 2],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimal_places' => 2, 'is_default' => false, 'sort_order' => 3],
        ] as $currency) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $currency['code']],
                $currency + ['is_active' => true, 'updated_at' => $now, 'created_at' => $now],
            );
        }

        $usdId = $this->id('currencies', 'code', 'USD');

        DB::table('countries')->updateOrInsert(
            ['code' => 'EG'],
            [
                'currency_id' => $usdId,
                'name' => 'Egypt',
                'phone_code' => '+20',
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $egyptId = $this->id('countries', 'code', 'EG');

        $destinations = [
            [
                'code' => 'hurghada',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80',
                'is_featured' => true,
                'sort_order' => 1,
                'translations' => [
                    'en' => ['name' => 'Hurghada', 'slug' => 'hurghada', 'short_description' => 'A Red Sea base for islands, reefs, desert safaris, and family activities.'],
                    'ar' => ['name' => 'الغردقة', 'slug' => 'hurghada', 'short_description' => 'وجهة على البحر الأحمر للرحلات البحرية والسفاري والأنشطة العائلية.'],
                ],
            ],
            [
                'code' => 'el-gouna',
                'image_url' => 'https://images.unsplash.com/photo-1590523277543-a94d2e4eb00b?auto=format&fit=crop&w=1200&q=80',
                'is_featured' => true,
                'sort_order' => 2,
                'translations' => [
                    'en' => ['name' => 'El Gouna', 'slug' => 'el-gouna', 'short_description' => 'A lagoon town with boutique cruises, calm beaches, and relaxed evening trips.'],
                    'ar' => ['name' => 'الجونة', 'slug' => 'el-gouna', 'short_description' => 'مدينة لاجونات هادئة لرحلات بحرية وتجارب مريحة.'],
                ],
            ],
            [
                'code' => 'luxor',
                'image_url' => 'https://images.unsplash.com/photo-1568322445389-f64ac2515020?auto=format&fit=crop&w=1200&q=80',
                'is_featured' => false,
                'sort_order' => 3,
                'translations' => [
                    'en' => ['name' => 'Luxor', 'slug' => 'luxor', 'short_description' => 'Ancient temples, Nile views, and full-day cultural tours from the Red Sea.'],
                    'ar' => ['name' => 'الأقصر', 'slug' => 'luxor', 'short_description' => 'معابد قديمة وإطلالات على النيل ورحلات ثقافية من البحر الأحمر.'],
                ],
            ],
        ];

        foreach ($destinations as $destination) {
            DB::table('destinations')->updateOrInsert(
                ['code' => $destination['code']],
                [
                    'country_id' => $egyptId,
                    'timezone' => 'Africa/Cairo',
                    'image_url' => $destination['image_url'],
                    'is_featured' => $destination['is_featured'],
                    'is_active' => true,
                    'sort_order' => $destination['sort_order'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $destinationId = $this->id('destinations', 'code', $destination['code']);
            foreach ($destination['translations'] as $locale => $translation) {
                DB::table('destination_translations')->updateOrInsert(
                    ['destination_id' => $destinationId, 'locale' => $locale],
                    $translation + ['updated_at' => $now, 'created_at' => $now],
                );
            }
        }

        $categories = [
            ['code' => 'sea-trips', 'image_url' => 'https://images.unsplash.com/photo-1502680390469-be75c86b636f?auto=format&fit=crop&w=1200&q=80', 'sort_order' => 1, 'translations' => ['en' => ['name' => 'Sea trips', 'slug' => 'sea-trips', 'description' => 'Island days, snorkeling stops, and Red Sea swimming.'], 'ar' => ['name' => 'رحلات بحرية', 'slug' => 'sea-trips', 'description' => 'جزر وسباحة وسنوركلينج في البحر الأحمر.']]],
            ['code' => 'desert-safari', 'image_url' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=1200&q=80', 'sort_order' => 2, 'translations' => ['en' => ['name' => 'Desert safari', 'slug' => 'desert-safari', 'description' => 'Quad bikes, Bedouin stops, and sunset desert scenes.'], 'ar' => ['name' => 'سفاري صحراوي', 'slug' => 'desert-safari', 'description' => 'دراجات رباعية ومحطات بدوية ومشاهد الغروب.']]],
            ['code' => 'cultural-tours', 'image_url' => 'https://images.unsplash.com/photo-1553913861-c0fddf2619ee?auto=format&fit=crop&w=1200&q=80', 'sort_order' => 3, 'translations' => ['en' => ['name' => 'Cultural tours', 'slug' => 'cultural-tours', 'description' => 'Temples, museums, Nile views, and guided history days.'], 'ar' => ['name' => 'رحلات ثقافية', 'slug' => 'cultural-tours', 'description' => 'معابد ومتاحف وإطلالات على النيل مع مرشدين.']]],
        ];

        foreach ($categories as $category) {
            DB::table('tour_categories')->updateOrInsert(
                ['code' => $category['code']],
                [
                    'image_url' => $category['image_url'] ?? null,
                    'is_featured' => true,
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $categoryId = $this->id('tour_categories', 'code', $category['code']);
            foreach ($category['translations'] as $locale => $translation) {
                DB::table('tour_category_translations')->updateOrInsert(
                    ['tour_category_id' => $categoryId, 'locale' => $locale],
                    $translation + ['updated_at' => $now, 'created_at' => $now],
                );
            }
        }

        $this->seedTours($admin->id, $now);
    }

    private function seedTours(int $adminId, mixed $now): void
    {
        $tours = [
            [
                'code' => 'orange-bay',
                'image_url' => 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=1200&q=80',
                'destination' => 'hurghada',
                'category' => 'sea-trips',
                'duration_value' => 8,
                'duration_unit' => 'hour',
                'tour_type' => 'shared',
                'is_featured' => true,
                'is_best_seller' => true,
                'translations' => [
                    'en' => ['name' => 'Orange Bay Snorkeling', 'slug' => 'orange-bay-snorkeling', 'short_description' => 'A full Red Sea island day with snorkeling stops.', 'description' => 'Sail from Hurghada to Orange Bay with guided snorkeling, beach time, lunch, and comfortable transfers.'],
                    'ar' => ['name' => 'سنوركلينج أورانج باي', 'slug' => 'orange-bay-snorkeling', 'short_description' => 'يوم كامل في الجزيرة مع توقفات سنوركلينج.', 'description' => 'رحلة بحرية من الغردقة إلى أورانج باي مع سنوركلينج وغداء وانتقالات مريحة.'],
                ],
                'languages' => ['en', 'ar', 'de'],
            ],
            [
                'code' => 'quad-bike-safari',
                'image_url' => 'https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?auto=format&fit=crop&w=1200&q=80',
                'destination' => 'hurghada',
                'category' => 'desert-safari',
                'duration_value' => 5,
                'duration_unit' => 'hour',
                'tour_type' => 'shared',
                'is_featured' => true,
                'is_last_minute' => true,
                'translations' => [
                    'en' => ['name' => 'Desert Safari Quad Bike', 'slug' => 'desert-safari-quad-bike', 'short_description' => 'Ride desert trails and enjoy sunset views.', 'description' => 'A guided desert adventure with quad biking, panoramic stops, and relaxed Bedouin-style hospitality.'],
                    'ar' => ['name' => 'سفاري دباب رباعي', 'slug' => 'desert-safari-quad-bike', 'short_description' => 'قيادة في الصحراء ومشاهدة الغروب.', 'description' => 'مغامرة صحراوية منظمة بدبابات رباعية وتوقفات بانورامية وضيافة بدوية.'],
                ],
                'languages' => ['en', 'ar', 'ru'],
            ],
            [
                'code' => 'luxor-full-day',
                'image_url' => 'https://images.unsplash.com/photo-1572252009286-268acec5ca0a?auto=format&fit=crop&w=1200&q=80',
                'destination' => 'luxor',
                'category' => 'cultural-tours',
                'duration_value' => 12,
                'duration_unit' => 'hour',
                'tour_type' => 'shared',
                'is_featured' => false,
                'is_best_seller' => true,
                'translations' => [
                    'en' => ['name' => 'Luxor Full-Day Tour', 'slug' => 'luxor-full-day-tour', 'short_description' => 'Temples, Nile views, and guided history in one long day.', 'description' => 'Travel from the Red Sea to Luxor for Karnak, the West Bank, Nile views, and guided cultural highlights.'],
                    'ar' => ['name' => 'رحلة الأقصر يوم كامل', 'slug' => 'luxor-full-day-tour', 'short_description' => 'معابد وإطلالات على النيل وتاريخ في يوم واحد.', 'description' => 'رحلة من البحر الأحمر إلى الأقصر لزيارة الكرنك والبر الغربي ومعالم ثقافية مع مرشد.'],
                ],
                'languages' => ['en', 'ar'],
            ],
        ];

        foreach ($tours as $tour) {
            DB::table('tours')->updateOrInsert(
                ['code' => $tour['code']],
                [
                    'destination_id' => $this->id('destinations', 'code', $tour['destination']),
                    'tour_category_id' => $this->id('tour_categories', 'code', $tour['category']),
                    'status' => 'published',
                    'image_url' => $tour['image_url'] ?? null,
                    'duration_value' => $tour['duration_value'],
                    'duration_unit' => $tour['duration_unit'],
                    'tour_type' => $tour['tour_type'],
                    'is_featured' => $tour['is_featured'] ?? false,
                    'is_best_seller' => $tour['is_best_seller'] ?? false,
                    'is_last_minute' => $tour['is_last_minute'] ?? false,
                    'published_at' => $now,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $tourId = $this->id('tours', 'code', $tour['code']);
            foreach ($tour['translations'] as $locale => $translation) {
                DB::table('tour_translations')->updateOrInsert(
                    ['tour_id' => $tourId, 'locale' => $locale],
                    $translation + [
                        'highlights' => json_encode(['Local guide', 'Organized pickup', 'Clear itinerary'], JSON_THROW_ON_ERROR),
                        'included' => json_encode(['Transfers', 'Guide support'], JSON_THROW_ON_ERROR),
                        'excluded' => json_encode(['Personal expenses'], JSON_THROW_ON_ERROR),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            foreach ($tour['languages'] as $languageCode) {
                DB::table('tour_operating_languages')->updateOrInsert(
                    ['tour_id' => $tourId, 'language_id' => $this->id('languages', 'code', $languageCode)],
                    ['updated_at' => $now, 'created_at' => $now],
                );
            }
        }
    }

    private function id(string $table, string $column, string $value): int
    {
        return (int) DB::table($table)->where($column, $value)->value('id');
    }
}
