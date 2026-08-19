<?php

declare(strict_types=1);

namespace App\Admin;

/**
 * Declares every setting the admin can edit: its group, its form control, its
 * validation rules, and the value to fall back to before anyone has saved one.
 *
 * The defaults here are the values that used to be hardcoded in
 * App\Services\Support\UiSettingsService — a phone number and address nobody
 * could change without a deploy. They stay as the fallback so a fresh install
 * renders exactly as before, and become editable the moment a row is saved.
 *
 * Translatable settings store a per-locale map: {"en": "...", "ar": "..."}.
 */
final class SettingSchema
{
    /**
     * Display order of the groups on the settings page.
     *
     * @var list<string>
     */
    public const GROUPS = ['company', 'contact', 'social'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        return [
            // --- company -------------------------------------------------
            'company_name' => [
                'group' => 'company',
                'type' => 'text',
                'label' => 'admin.settings.fields.company_name',
                'rules' => ['required', 'string', 'max:255'],
                'default' => 'Hurgada guide',
            ],
            'company_description' => [
                'group' => 'company',
                'type' => 'textarea',
                'label' => 'admin.settings.fields.company_description',
                'rules' => ['nullable', 'string', 'max:500'],
                'translatable' => true,
                'default' => null,
            ],
            'company_address' => [
                'group' => 'company',
                'type' => 'text',
                'label' => 'admin.settings.fields.company_address',
                'rules' => ['nullable', 'string', 'max:255'],
                'translatable' => true,
                'default' => null,
            ],

            // --- contact -------------------------------------------------
            'contact_phone' => [
                'group' => 'contact',
                'type' => 'text',
                'label' => 'admin.settings.fields.contact_phone',
                'rules' => ['nullable', 'string', 'max:32'],
                'default' => '+20 100 000 0000',
            ],
            'contact_whatsapp' => [
                'group' => 'contact',
                'type' => 'text',
                'label' => 'admin.settings.fields.contact_whatsapp',
                'rules' => ['nullable', 'string', 'max:32'],
                'help' => 'admin.settings.fields.contact_whatsapp_help',
                'default' => '+201000000000',
            ],
            'contact_email' => [
                'group' => 'contact',
                'type' => 'text',
                'label' => 'admin.settings.fields.contact_email',
                'rules' => ['nullable', 'email', 'max:255'],
                'default' => 'Tourshurgada@gmail.com',
            ],

            // --- social --------------------------------------------------
            'social_facebook' => [
                'group' => 'social',
                'type' => 'text',
                'label' => 'admin.settings.fields.social_facebook',
                'rules' => ['nullable', 'url', 'max:2048'],
                'default' => null,
            ],
            'social_instagram' => [
                'group' => 'social',
                'type' => 'text',
                'label' => 'admin.settings.fields.social_instagram',
                'rules' => ['nullable', 'url', 'max:2048'],
                'default' => null,
            ],
            'social_youtube' => [
                'group' => 'social',
                'type' => 'text',
                'label' => 'admin.settings.fields.social_youtube',
                'rules' => ['nullable', 'url', 'max:2048'],
                'default' => null,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fieldsInGroup(string $group): array
    {
        return array_filter(
            self::fields(),
            static fn (array $field): bool => $field['group'] === $group,
        );
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::fields());
    }

    public static function isTranslatable(string $key): bool
    {
        return (self::fields()[$key]['translatable'] ?? false) === true;
    }

    /**
     * @return list<string>
     */
    public static function translatableKeys(): array
    {
        return array_keys(array_filter(
            self::fields(),
            static fn (array $field): bool => ($field['translatable'] ?? false) === true,
        ));
    }

    public static function default(string $key): mixed
    {
        return self::fields()[$key]['default'] ?? null;
    }
}
