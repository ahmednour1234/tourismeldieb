<x-layouts.admin :title="$title">
    <x-admin.page-header :title="$model->reference" />

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        {{-- What the customer actually asked for. Read-only on purpose: staff
             triage a request, they do not rewrite it. --}}
        <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.bookings.request') }}</h2>

            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('admin.bookings.tour') }}</dt>
                    <dd class="mt-1 text-slate-950 dark:text-white">
                        {{ $model->tour?->translation?->name ?? $model->tour?->code ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('admin.bookings.customer') }}</dt>
                    <dd class="mt-1 text-slate-950 dark:text-white">{{ $model->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('admin.fields.email') }}</dt>
                    <dd class="mt-1">
                        <a href="mailto:{{ $model->customer_email }}" class="text-teal-700 hover:underline dark:text-teal-400">
                            {{ $model->customer_email }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('website.forms.phone') }}</dt>
                    <dd class="mt-1">
                        @if ($model->customer_phone)
                            <a href="tel:{{ preg_replace('/[^+\d]/', '', $model->customer_phone) }}" class="text-teal-700 hover:underline dark:text-teal-400">
                                {{ $model->customer_phone }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('website.booking.preferred_date') }}</dt>
                    <dd class="mt-1 text-slate-950 dark:text-white">{{ $model->preferred_date->isoFormat('D MMMM YYYY') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('website.booking.guests') }}</dt>
                    <dd class="mt-1 text-slate-950 dark:text-white">
                        {{ __('admin.bookings.guest_breakdown', [
                            'adults' => $model->adults,
                            'children' => $model->children,
                            'infants' => $model->infants,
                        ]) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('admin.bookings.submitted') }}</dt>
                    <dd class="mt-1 text-slate-950 dark:text-white">
                        {{ $model->created_at?->diffForHumans() }}
                        <span class="text-slate-400">· {{ mb_strtoupper($model->locale) }}</span>
                    </dd>
                </div>

                @if ($model->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('website.booking.notes') }}</dt>
                        <dd class="mt-1 whitespace-pre-line rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $model->notes }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        {{-- The only part staff may change. --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <form method="POST" action="{{ route('admin.bookings.update', $id) }}" class="rounded-lg bg-white p-6 shadow-sm dark:bg-slate-900">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ __('admin.bookings.triage') }}</h2>

                <div class="mt-4 grid gap-4">
                    @foreach ($fields as $name => $field)
                        <x-admin.schema-field
                            :name="$name"
                            :field="$field"
                            :value="$values[$name] ?? null"
                        />
                    @endforeach
                </div>

                @if ($model->handled_at)
                    <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
                        {{ __('admin.bookings.last_handled', [
                            'who' => $model->handler?->name ?? '—',
                            'when' => $model->handled_at->diffForHumans(),
                        ]) }}
                    </p>
                @endif

                <x-public.button type="submit" class="mt-5 w-full justify-center">{{ __('admin.actions.save') }}</x-public.button>
                <a href="{{ route('admin.bookings.index') }}" class="mt-3 block text-center text-sm font-semibold text-slate-500 hover:underline">
                    {{ __('admin.actions.cancel') }}
                </a>
            </form>
        </aside>
    </div>
</x-layouts.admin>
