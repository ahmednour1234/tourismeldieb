<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-5xl px-4 py-12">
        <header class="max-w-2xl">
            <h1 class="text-3xl font-bold text-slate-950">{{ __('website.booking.title') }}</h1>
            <p class="mt-2 text-slate-600">{{ __('website.booking.subtitle') }}</p>
        </header>

        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4" role="alert">
                <p class="text-sm font-semibold text-red-800">{{ __('website.booking.fix_errors') }}</p>
                <ul class="mt-2 list-inside list-disc text-sm text-red-700">
                    @foreach ($errors->unique() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($tours === [])
            <p class="mt-8 rounded-lg border border-dashed border-slate-300 p-12 text-center text-slate-500">
                {{ __('website.booking.no_tours') }}
            </p>
        @else
            <form method="POST" action="{{ route('booking.store', ['locale' => app()->getLocale()]) }}"
                  class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]"
                  x-data="{ tour: '{{ old('tour_id', $selectedTourId) }}' }">
                @csrf

                <div class="space-y-6">
                    <fieldset class="rounded-lg bg-white p-6 shadow-sm">
                        <legend class="px-2 text-sm font-bold text-slate-900">{{ __('website.booking.your_trip') }}</legend>

                        <div class="grid gap-4">
                            <div>
                                <x-forms.label for="tour_id" :required="true">{{ __('website.booking.tour') }}</x-forms.label>
                                <select id="tour_id" name="tour_id" required x-model="tour"
                                        class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700">
                                    <option value="">{{ __('website.booking.choose_tour') }}</option>
                                    @foreach ($tours as $tour)
                                        <option value="{{ $tour['id'] }}">
                                            {{ $tour['name'] }} — {{ $tour['destination'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-forms.error name="tour_id" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-forms.input
                                    name="preferred_date"
                                    type="date"
                                    :label="__('website.booking.preferred_date')"
                                    :value="old('preferred_date')"
                                    :min="now()->toDateString()"
                                    required
                                />
                                <x-forms.input
                                    name="adults"
                                    type="number"
                                    :label="__('website.booking.adults')"
                                    :value="old('adults', 2)"
                                    min="1"
                                    max="40"
                                    required
                                />
                                <x-forms.input
                                    name="children"
                                    type="number"
                                    :label="__('website.booking.children')"
                                    :value="old('children', 0)"
                                    min="0"
                                    max="40"
                                    :help="__('website.booking.children_help')"
                                />
                                <x-forms.input
                                    name="infants"
                                    type="number"
                                    :label="__('website.booking.infants')"
                                    :value="old('infants', 0)"
                                    min="0"
                                    max="20"
                                    :help="__('website.booking.infants_help')"
                                />
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="rounded-lg bg-white p-6 shadow-sm">
                        <legend class="px-2 text-sm font-bold text-slate-900">{{ __('website.booking.your_details') }}</legend>

                        <div class="grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-forms.input
                                    name="customer_name"
                                    :label="__('website.forms.name')"
                                    :value="old('customer_name', auth()->user()?->name)"
                                    autocomplete="name"
                                    required
                                />
                                <x-forms.input
                                    name="customer_email"
                                    type="email"
                                    :label="__('website.forms.email')"
                                    :value="old('customer_email', auth()->user()?->email)"
                                    autocomplete="email"
                                    required
                                />
                            </div>
                            <x-forms.phone
                                name="customer_phone"
                                :label="__('website.forms.phone')"
                                :help="__('website.booking.phone_help')"
                            />
                            <x-forms.textarea
                                name="notes"
                                :label="__('website.booking.notes')"
                                :value="old('notes')"
                                :help="__('website.booking.notes_help')"
                            />
                        </div>
                    </fieldset>
                </div>

                {{-- Summary rail: reflects the chosen tour without a round trip. --}}
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-bold text-slate-900">{{ __('website.booking.summary') }}</h2>

                        @foreach ($tours as $tour)
                            <div x-cloak x-show="tour === '{{ $tour['id'] }}'" class="mt-4">
                                <x-public.image :src="$tour['image']" class="h-32 w-full rounded-md object-cover" />
                                <p class="mt-3 font-bold text-slate-950">{{ $tour['name'] }}</p>
                                <p class="text-sm text-slate-500">{{ $tour['destination'] }} · {{ $tour['duration'] }}</p>
                                <p class="mt-3 rounded-md bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
                                    {{ $tour['starting_price_label'] ?? __('website.price_soon') }}
                                </p>
                            </div>
                        @endforeach

                        <p x-cloak x-show="! tour" class="mt-4 text-sm text-slate-500">
                            {{ __('website.booking.choose_tour_hint') }}
                        </p>

                        {{-- Set expectations honestly: this is a request, and no
                             seat is held until an operator confirms it. --}}
                        <p class="mt-4 rounded-md border border-teal-100 bg-teal-50 p-3 text-xs leading-relaxed text-teal-900">
                            {{ __('website.booking.no_payment_notice') }}
                        </p>

                        <button type="submit"
                                class="mt-4 w-full rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">
                            {{ __('website.booking.submit') }}
                        </button>
                    </div>
                </aside>
            </form>
        @endif
    </section>
</x-layouts.public>
