<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Admin\ResourceSchema;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Builds one resource's validation rules from ResourceSchema.
 *
 * The rules are not restated here. They live beside the field definitions, so a
 * field added to the schema is validated the moment it renders, rather than
 * silently accepting anything until someone remembers to update a request class.
 */
final class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resource = $this->resourceName();
        $ability = $this->routeIs('*.store') ? 'create' : 'update';

        return (bool) $this->user()?->can("{$resource}.{$ability}");
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $resource = $this->resourceName();

        abort_unless(ResourceSchema::exists($resource), 404);

        $rules = [];

        foreach (ResourceSchema::fields($resource) as $field => $definition) {
            $rules[$field] = $this->fieldRules($resource, $field, $definition);
        }

        foreach ($this->localeCodes() as $locale) {
            foreach (ResourceSchema::translationFields($resource) as $field => $definition) {
                /** @var list<string> $fieldRules */
                $fieldRules = $definition['rules'] ?? [];

                $rules["translations.{$locale}.{$field}"] = $this->translationRules($fieldRules, $locale);
            }
        }

        return $rules;
    }

    /**
     * Toggles absent from the payload mean "off" — an unchecked checkbox sends
     * nothing at all, which would otherwise read as "leave unchanged".
     */
    protected function prepareForValidation(): void
    {
        $resource = $this->resourceName();

        if (! ResourceSchema::exists($resource)) {
            return;
        }

        $normalised = [];

        foreach (ResourceSchema::fields($resource) as $field => $definition) {
            if (($definition['type'] ?? null) === 'toggle') {
                $normalised[$field] = $this->boolean($field);
            }
        }

        if ($normalised !== []) {
            $this->merge($normalised);
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return list<mixed>
     */
    private function fieldRules(string $resource, string $field, array $definition): array
    {
        /** @var list<mixed> $rules */
        $rules = $definition['rules'] ?? [];

        if (($definition['unique'] ?? false) === true) {
            $table = ResourceSchema::table($resource);
            $unique = Rule::unique($table, $field);
            $id = $this->route('id');

            if ($id !== null) {
                $unique->ignore($id);
            }

            // Soft-deleted rows keep their code, so without this a deleted
            // "EGP" would block ever creating "EGP" again — and the admin
            // cannot see the row that is blocking them.
            if (Schema::hasColumn($table, 'deleted_at')) {
                $unique->whereNull('deleted_at');
            }

            // Some codes are only unique within a parent: a tour package's
            // code is unique per tour, so two different tours may each have a
            // "private" package. Checking it table-wide would reject the
            // second one for colliding with an unrelated tour's package.
            $scope = $definition['unique_scope'] ?? null;

            if (is_string($scope)) {
                $unique->where($scope, $this->input($scope));
            }

            $rules[] = $unique;
        }

        // A password is required when creating a user and optional thereafter.
        if ($resource === 'users' && $field === 'password' && $this->routeIs('*.store')) {
            $rules = array_values(array_filter(
                $rules,
                fn (mixed $rule): bool => $rule !== 'nullable',
            ));
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    /**
     * Translated fields are only required for the default locale; a secondary
     * locale left entirely blank is skipped rather than rejected.
     *
     * @param  list<string>  $rules
     * @return list<string>
     */
    private function translationRules(array $rules, string $locale): array
    {
        // Keyed on the fallback locale, not app.locale: the fallback is the one
        // guaranteed to render on every public page, so it is the one that must
        // be complete. app.locale merely reflects the current request.
        if ($locale === config('app.fallback_locale')) {
            return $rules;
        }

        return array_values(array_map(
            fn (string $rule): string => $rule === 'required' ? 'nullable' : $rule,
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

        return $codes === [] ? [(string) config('app.locale')] : $codes;
    }

    private function resourceName(): string
    {
        return (string) $this->route('resource');
    }
}
