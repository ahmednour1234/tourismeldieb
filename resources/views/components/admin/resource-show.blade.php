@props(['resource', 'title', 'id', 'item' => null, 'fields' => [], 'translationFields' => [], 'translationValues' => [], 'languages' => []])

<x-layouts.admin :title="$title">
    <x-admin.page-header
        :title="$title"
        :action="auth()->user()?->can($resource.'.update')
            ? ['href' => route('admin.'.$resource.'.edit', $id), 'label' => __('admin.actions.edit')]
            : null"
    />

    <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-slate-900">
        <dl class="grid gap-5 md:grid-cols-2">
            @foreach ($fields as $name => $field)
                {{-- A password hash is never anyone's business, not even an admin's. --}}
                @continue($field['type'] === 'password')
                @php($value = $item?->getAttribute($name))
                {{-- Money is stored in minor units, so the raw attribute would
                     read as 1950 where the admin typed 19.50. --}}
                @php($value = $field['type'] === 'money'
                    ? \App\Admin\Money::toMajor($value, $item?->getAttribute('currency_id'))
                    : $value)
                <div @class(['md:col-span-2' => $field['type'] === 'textarea'])>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __($field['label']) }}</dt>
                    <dd class="mt-1 text-slate-950 dark:text-white">
                        @if ($field['type'] === 'toggle')
                            <x-admin.badge>{{ $value ? __('admin.fields.is_active') : '—' }}</x-admin.badge>
                        @elseif ($value === null || $value === '')
                            <span class="text-slate-400">—</span>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>

    @if ($translationFields !== [] && $languages !== [])
        <section class="mt-6 rounded-lg bg-white p-6 shadow-sm dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.translations.title') }}</h2>
            <div class="mt-4 space-y-4">
                @foreach ($languages as $language)
                    @php($values = $translationValues[$language['code']] ?? null)
                    <div dir="{{ $language['direction'] }}" class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $language['native'] }}</span>
                            <x-admin.badge>{{ $values ? __('admin.translations.complete') : __('admin.translations.missing') }}</x-admin.badge>
                        </div>
                        @if ($values)
                            <dl class="mt-3 grid gap-3">
                                @foreach ($translationFields as $name => $field)
                                    @continue(blank($values[$name] ?? null))
                                    <div>
                                        <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __($field['label']) }}</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 dark:text-slate-100">{{ $values[$name] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.admin>
