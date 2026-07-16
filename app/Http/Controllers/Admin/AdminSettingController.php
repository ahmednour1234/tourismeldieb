<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\UpdateSettingsAction;
use App\Admin\SettingSchema;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Language;
use App\Shared\Contracts\SettingRepositoryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Settings is a single form, not a CRUD resource: there is one row per key and
 * nothing to list, create, or delete. It gets its own controller rather than
 * being bent into AdminResourceController's list/create/edit shape.
 */
final class AdminSettingController
{
    public function __construct(
        private readonly SettingRepositoryContract $repository,
    ) {}

    public function edit(): View
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        return view('admin.settings.index', [
            'resource' => 'settings',
            'title' => __('admin.resources.settings'),
            'groups' => $this->groups(),
            'values' => $this->values(),
            'languages' => $this->languages(),
            'canUpdate' => (bool) auth()->user()?->can('settings.update'),
        ]);
    }

    public function update(SettingRequest $request, UpdateSettingsAction $update): RedirectResponse
    {
        // Authorization already ran in SettingRequest::authorize().
        $update($request->validated());

        return redirect()
            ->route('admin.settings.index')
            ->with('success', __('admin.crud.updated'));
    }

    /**
     * Fields grouped for display, in the schema's declared group order.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function groups(): array
    {
        $groups = [];

        foreach (SettingSchema::GROUPS as $group) {
            $fields = SettingSchema::fieldsInGroup($group);

            if ($fields !== []) {
                $groups[$group] = $fields;
            }
        }

        return $groups;
    }

    /**
     * Current values, falling back to each field's schema default so a fresh
     * install renders the same content it did when these were hardcoded.
     *
     * Translatable keys keep their whole locale map: the form renders one
     * input per locale, so it needs every translation, not just the active one.
     *
     * @return array<string, mixed>
     */
    private function values(): array
    {
        $stored = $this->repository->all();
        $values = [];

        foreach (array_keys(SettingSchema::fields()) as $key) {
            $values[$key] = $stored[$key] ?? SettingSchema::default($key);
        }

        return $values;
    }

    /**
     * @return list<array{code: string, native: string, direction: string}>
     */
    private function languages(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Language $language): array => [
                'code' => $language->code,
                'native' => $language->native_name,
                'direction' => $language->direction,
            ])
            ->all();
    }
}
