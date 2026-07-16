@props([
    'resource',
    'title',
    'id' => null,
    'fields' => [],
    'translationFields' => [],
    'values' => [],
    'translationValues' => [],
    'relationOptions' => [],
    'languages' => [],
])

{{--
    The create/edit form for every admin resource.

    Fields come from App\Admin\ResourceSchema via the controller, so this
    renders each resource's *real* columns rather than the old hardcoded
    name/code/status/active set that matched almost no table.
--}}
<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" />

    {{-- Errors are rendered once by <x-admin.flash-message> in the admin
         layout; a second banner here would duplicate every message. --}}
    <form
        method="POST"
        action="{{ $id ? route('admin.'.$resource.'.update', $id) : route('admin.'.$resource.'.store') }}"
        class="space-y-6"
        x-data="{ dirty: false }"
        x-on:input="dirty = true"
        x-on:submit="dirty = false"
        x-on:beforeunload.window="if (dirty) $event.preventDefault()"
    >
        @csrf
        @if ($id)
            @method('PUT')
        @endif

        <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-slate-900">
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($fields as $name => $field)
                    <div @class(['md:col-span-2' => in_array($field['type'], ['textarea'], true)])>
                        <x-admin.schema-field
                            :name="$name"
                            :field="$field"
                            :value="$values[$name] ?? null"
                            :options="$relationOptions[$name] ?? []"
                        />
                    </div>
                @endforeach
            </div>
        </section>

        @if ($translationFields !== [] && $languages !== [])
            {{--
                Only the fallback locale's fields are marked required, mirroring
                ResourceRequest exactly. Marking every locale required made the
                browser silently refuse to submit — the blocking input sits on a
                hidden tab, so it reports "Please fill out this field" against an
                element nobody can see, and the Save button appears to do nothing.
            --}}
            @php($requiredLocale = (string) config('app.fallback_locale'))
            <x-forms.translation-tabs :languages="$languages">
                @foreach ($languages as $language)
                    <div x-show="tab === '{{ $language['code'] }}'" x-cloak dir="{{ $language['direction'] }}" class="grid gap-4">
                        @foreach ($translationFields as $name => $field)
                            <x-admin.schema-field
                                :name="'translations['.$language['code'].']['.$name.']'"
                                :field="$language['code'] === $requiredLocale
                                    ? $field
                                    : array_merge($field, ['rules' => array_values(array_diff($field['rules'] ?? [], ['required']))])"
                                :value="$translationValues[$language['code']][$name] ?? null"
                            />
                        @endforeach
                    </div>
                @endforeach
            </x-forms.translation-tabs>
        @endif

        <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
            <x-public.button type="submit">{{ __('admin.actions.save') }}</x-public.button>
            <x-public.button type="submit" name="continue" value="1" variant="secondary">{{ __('admin.actions.save_continue') }}</x-public.button>
            <x-public.button :href="route('admin.'.$resource.'.index')" variant="secondary">{{ __('admin.actions.cancel') }}</x-public.button>
        </div>
    </form>

    @if ($id && ! \App\Admin\ResourceSchema::isReadOnlyOrigin($resource))
        @if (auth()->user()?->can($resource.'.delete'))
            <form
                method="POST"
                action="{{ route('admin.'.$resource.'.destroy', $id) }}"
                class="mt-6 rounded-lg border border-red-200 bg-white p-6 shadow-sm dark:border-red-900/50 dark:bg-slate-900"
                x-on:submit="$event.submitter.disabled = true"
                onsubmit="return confirm(@js(__('admin.crud.confirm_delete')))"
            >
                @csrf
                @method('DELETE')
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ __('admin.crud.danger_zone') }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.crud.delete_help') }}</p>
                <button type="submit" class="mt-3 rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/40">
                    {{ __('admin.actions.delete') }}
                </button>
            </form>
        @endif
    @endif
</x-layouts.admin>
