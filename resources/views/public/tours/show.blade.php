<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <x-public.breadcrumb :items="[['label' => __('website.nav.home'), 'url' => route('home', app()->getLocale())], ['label' => $tour['destination'], 'url' => route('destinations.show', [app()->getLocale(), $tour['destination_slug']])], ['label' => $tour['name']]]" />
        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]">
            <article>
                <x-public.image :src="$tour['image']" class="aspect-[16/8] w-full rounded-lg object-cover" />
                <div class="mt-6 flex flex-wrap gap-2">
                    @if ($tour['featured'])<x-public.badge>{{ __('website.tours.featured') }}</x-public.badge>@endif
                    @if ($tour['best_seller'])<x-public.badge tone="amber">{{ __('website.tours.best_seller') }}</x-public.badge>@endif
                </div>
                <h1 class="mt-4 text-4xl font-black text-slate-950">{{ $tour['name'] }}</h1>
                <p class="mt-3 text-lg text-slate-600">{{ $tour['short_description'] }}</p>

                {{-- Every section below renders only when the tour actually has
                     that content. This block previously printed the company
                     boilerplate under nine different headings and "Booking will
                     be available soon" under two more, which told a visitor
                     nothing about the tour and read as though it were not on
                     sale. An unwritten section is now simply absent. --}}
                @if ($tour['description'] !== '')
                    <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-950">{{ __('website.tours.details') }}</h2>
                        <p class="mt-3 whitespace-pre-line text-slate-600">{{ $tour['description'] }}</p>
                    </section>
                @endif

                @foreach ([
                    __('website.tours.highlights') => $tour['highlights'],
                    __('website.tours.itinerary') => $tour['itinerary'],
                    __('website.tours.included') => $tour['included'],
                    __('website.tours.excluded') => $tour['excluded'],
                ] as $heading => $items)
                    @continue($items === [])
                    <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-950">{{ $heading }}</h2>
                        <ul class="mt-3 space-y-2 text-slate-600">
                            @foreach ($items as $item)
                                <li class="flex gap-2">
                                    <span aria-hidden="true" class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-teal-700"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach

                {{-- Site-wide policies, edited once in admin Settings. Each is
                     omitted entirely until it has been written. --}}
                @foreach ([
                    __('website.tours.cancellation') => $settings['policy_cancellation'] ?? '',
                    __('website.tours.important') => $settings['policy_important'] ?? '',
                ] as $heading => $policy)
                    @continue($policy === '')
                    <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-950">{{ $heading }}</h2>
                        <p class="mt-3 whitespace-pre-line text-slate-600">{{ $policy }}</p>
                    </section>
                @endforeach

                @if ($tour['faqs'] !== [])
                    <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-950">{{ __('website.nav.faq') }}</h2>
                        <div class="mt-3 space-y-4">
                            @foreach ($tour['faqs'] as $faq)
                                <div>
                                    <h3 class="font-semibold text-slate-900">{{ $faq['question'] }}</h3>
                                    <p class="mt-1 whitespace-pre-line text-slate-600">{{ $faq['answer'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    @if ($tour['duration'] !== '')
                        <p class="text-sm font-semibold text-slate-500">{{ $tour['duration'] }}</p>
                    @endif
                    <p class="mt-3 rounded-md bg-slate-50 px-3 py-2 font-semibold text-slate-700">{{ $tour['starting_price_label'] ?? __('website.price_soon') }}</p>
                    <p class="mt-3 text-sm text-slate-600">{{ __('website.booking.no_payment_notice') }}</p>
                    <x-public.button
                        class="mt-5 w-full justify-center"
                        :href="route('booking.create', ['locale' => app()->getLocale(), 'tour' => $tour['slug']])"
                    >{{ __('website.book_now') }}</x-public.button>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($tour['languages'] as $language)
                            <x-public.language-badge :language="$language" />
                        @endforeach
                    </div>
                </div>
                @if (($options ?? []) !== [])
                    <div class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-950">{{ __('website.tours.options') }}</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($options as $option)
                                <article class="rounded-md border border-slate-200 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-bold text-slate-950">{{ $option['name'] }}</h3>
                                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $option['short_description'] }}</p>
                                        </div>
                                        <x-public.badge :tone="$option['is_private'] ? 'amber' : 'teal'">
                                            {{ $option['is_private'] ? __('website.tours.private') : __('website.tours.shared') }}
                                        </x-public.badge>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-700">
                                        @if ($option['duration'] !== '')<span>{{ $option['duration'] }}</span>@endif
                                        <span>{{ __('website.tours.capacity') }}: {{ $option['capacity'] }}</span>
                                        <span class="font-semibold text-teal-800">{{ $option['price_label'] ?? __('website.price_soon') }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-4 py-12">
        <x-public.section-heading :title="__('website.tours.related')" />
        <div class="grid gap-5 lg:grid-cols-3">
            @foreach (array_slice($relatedTours, 0, 3) as $relatedTour)
                <x-public.tour-card :tour="$relatedTour" />
            @endforeach
        </div>
    </section>
</x-layouts.public>
