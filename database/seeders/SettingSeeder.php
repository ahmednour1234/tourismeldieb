<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Admin\SettingSchema;
use App\Models\Setting;
use App\Shared\Contracts\SettingRepositoryContract;
use Illuminate\Database\Seeder;

/**
 * Seeds the settings that used to be hardcoded in UiSettingsService.
 *
 * `firstOrCreate` rather than `updateOrCreate`: re-seeding must not overwrite
 * values an admin has since edited.
 */
final class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $seeded = [
            'company_name' => 'Hurgada guide',
            'company_description' => [
                'en' => 'Day tours, excursions and transfers across the Egyptian Red Sea.',
                'ar' => 'رحلات يومية ومغامرات وانتقالات في جميع أنحاء البحر الأحمر المصري.',
            ],
            'company_address' => [
                'en' => 'Sheraton Road, Hurghada, Red Sea Governorate, Egypt',
                'ar' => 'شارع شيراتون، الغردقة، محافظة البحر الأحمر، مصر',
            ],
            'contact_phone' => '+20 100 000 0000',
            'contact_whatsapp' => '+201000000000',
            'contact_email' => 'hello@hurgadaguide.example',
        ];

        foreach ($seeded as $key => $value) {
            if (! SettingSchema::has($key)) {
                continue;
            }

            Setting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => SettingSchema::fields()[$key]['group'],
                    'value' => $value,
                    'is_translatable' => SettingSchema::isTranslatable($key),
                ],
            );
        }

        app(SettingRepositoryContract::class)->flushCache();
    }
}
