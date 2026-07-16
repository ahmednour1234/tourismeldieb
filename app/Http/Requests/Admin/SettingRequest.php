<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Admin\SettingSchema;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the settings form from SettingSchema.
 */
final class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('settings.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (SettingSchema::fields() as $key => $field) {
            /** @var list<string> $fieldRules */
            $fieldRules = $field['rules'] ?? [];

            if (($field['translatable'] ?? false) !== true) {
                $rules[$key] = $fieldRules;

                continue;
            }

            // A translatable setting posts one input per locale. Only the
            // fallback locale carries the field's own rules; the others are
            // optional, so a half-translated site can still be saved.
            $rules[$key] = ['array'];

            foreach ($this->localeCodes() as $locale) {
                $rules[$key.'.'.$locale] = $locale === (string) config('app.fallback_locale')
                    ? $fieldRules
                    : $this->optional($fieldRules);
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (SettingSchema::fields() as $key => $field) {
            $attributes[$key] = __($field['label']);

            if (($field['translatable'] ?? false) === true) {
                foreach ($this->localeCodes() as $locale) {
                    $attributes[$key.'.'.$locale] = __($field['label']).' ('.mb_strtoupper($locale).')';
                }
            }
        }

        return $attributes;
    }

    /**
     * @param  list<string>  $rules
     * @return list<string>
     */
    private function optional(array $rules): array
    {
        return array_values(array_map(
            static fn (string $rule): string => $rule === 'required' ? 'nullable' : $rule,
            $rules,
        ));
    }

    /**
     * @return list<string>
     */
    private function localeCodes(): array
    {
        /** @var list<string> $codes */
        $codes = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();

        return $codes === [] ? [(string) config('app.fallback_locale')] : $codes;
    }
}
