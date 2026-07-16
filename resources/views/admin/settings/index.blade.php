<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$title" />

    {{--
        Settings is one form, not a listing: there is one row per key, nothing
        to create and nothing to delete. Fields come from App\Admin\SettingSchema.
    --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6" x-data="{ dirty: false }" x-on:input="dirty = true" x-on:submit="dirty = false">
        @csrf
        @method('PUT')

        @foreach ($groups as $group => $fields)
            <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.settings.groups.'.$group) }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.settings.groups.'.$group.'_help') }}</p>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($fields as $key => $field)
                        @if ($field['translatable'] ?? false)
                            {{-- One input per active locale, each labelled with
                                 its language and rendered in that language's
                                 own direction. --}}
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __($field['label']) }}</p>
                                <div class="mt-2 grid gap-3 md:grid-cols-2">
                                    @foreach ($languages as $language)
                                        <div dir="{{ $language['direction'] }}">
                                            <x-admin.schema-field
                                                :name="$key.'['.$language['code'].']'"
                                                :field="$field"
                                                :value="is_array($values[$key] ?? null) ? ($values[$key][$language['code']] ?? null) : null"
                                                :label-override="$language['native']"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div @class(['md:col-span-2' => $field['type'] === 'textarea'])>
                                <x-admin.schema-field
                                    :name="$key"
                                    :field="$field"
                                    :value="$values[$key] ?? null"
                                />
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($canUpdate)
            <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                <x-public.button type="submit">{{ __('admin.actions.save') }}</x-public.button>
            </div>
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('admin.settings.read_only') }}</p>
        @endif
    </form>
</x-layouts.admin>
