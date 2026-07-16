@props(['resource', 'title', 'items', 'search' => null])

<x-layouts.admin :title="$title">
    {{-- No "Add" for a resource whose rows originate with customers. --}}
    <x-admin.page-header
        :title="$title"
        :action="auth()->user()?->can($resource.'.create') && ! \App\Admin\ResourceSchema::isReadOnlyOrigin($resource)
            ? ['href' => route('admin.'.$resource.'.create'), 'label' => __('admin.actions.add')]
            : null"
    />

    <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-slate-900">
        {{-- Search submits via GET so results stay linkable and bookmarkable. --}}
        <form method="GET" class="mb-4 flex gap-3">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                class="w-full max-w-sm rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-950"
                placeholder="{{ __('admin.crud.search_placeholder') }}"
                aria-label="{{ __('admin.actions.search') }}"
            >
            <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                {{ __('admin.actions.search') }}
            </button>
            @if ($search)
                <a href="{{ route('admin.'.$resource.'.index') }}" class="self-center text-sm font-semibold text-teal-700 hover:underline dark:text-teal-400">
                    {{ __('admin.actions.cancel') }}
                </a>
            @endif
        </form>

        @if ($items->isEmpty())
            <x-admin.empty-state>
                {{ $search ? __('admin.crud.no_results') : __('admin.crud.empty') }}
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-slate-500">
                            <th scope="col" class="py-3 text-start">{{ $resource === 'bookings' ? __('admin.bookings.reference') : __('admin.fields.name') }}</th>
                            <th scope="col" class="py-3 text-start">{{ $resource === 'bookings' ? __('admin.bookings.customer') : __('admin.fields.code') }}</th>
                            <th scope="col" class="py-3 text-start">{{ __('admin.fields.status') }}</th>
                            <th scope="col" class="py-3 text-end">{{ __('admin.actions.edit') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($items as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="py-3 font-medium text-slate-800 dark:text-slate-100">{{ $item['name'] }}</td>
                                <td class="py-3 text-slate-500 dark:text-slate-400">{{ $item['code'] ?? '—' }}</td>
                                {{-- The status is a raw column value (active /
                                     draft / published), so it needs translating
                                     before display — it read as English text in
                                     an otherwise Arabic table.

                                     Bookings have their own status vocabulary;
                                     falling back to the key itself would render
                                     a literal "admin.status.pending" on screen. --}}
                                <td class="py-3">
                                    @if ($item['status'] !== null)
                                        @php($statusKey = 'admin.'.($resource === 'bookings' ? 'booking_status' : 'status').'.'.$item['status'])
                                        <x-admin.badge>{{ __($statusKey) === $statusKey ? $item['status'] : __($statusKey) }}</x-admin.badge>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                {{-- Permissions are checked by name, not via a
                                     policy: these routes are keyed by a resource
                                     slug rather than a bound model, so
                                     @can('update', $resource) would hand Gate a
                                     string it cannot resolve — and silently
                                     deny every row. --}}
                                <td class="py-3 text-end">
                                    @if (auth()->user()?->can($resource.'.update'))
                                        <a href="{{ route('admin.'.$resource.'.edit', $item['id']) }}" class="font-semibold text-teal-700 hover:underline dark:text-teal-400">
                                            {{ __('admin.actions.edit') }}
                                        </a>
                                    @else
                                        <a href="{{ route('admin.'.$resource.'.show', $item['id']) }}" class="font-semibold text-teal-700 hover:underline dark:text-teal-400">
                                            {{ __('admin.actions.view') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </section>
</x-layouts.admin>
