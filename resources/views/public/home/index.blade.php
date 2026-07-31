<x-layouts.public :seo="$seo">
    <section class="relative isolate flex min-h-[88vh] items-center overflow-hidden bg-slate-950 text-white">
        {{-- Slow Ken Burns drift on a self-hosted image (no external hotlink,
             no video file). The wrapper is scaled so the pan never exposes an
             edge. --}}
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <img
                class="hero-media h-full w-full object-cover"
                src="{{ asset('images/hero/red-sea.jpg') }}"
                alt=""
                fetchpriority="high"
            >
        </div>

        {{-- Layered wash: a deep diagonal for text contrast, plus a breathing
             teal aurora for life. Logical-property gradient so it leans from
             the reading edge in both LTR and RTL. --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/85 via-slate-950/35 to-transparent"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-teal-950/45 via-transparent to-transparent"></div>
        <div class="hero-aurora pointer-events-none absolute -start-1/4 top-0 -z-10 h-2/3 w-2/3 rounded-full bg-teal-400/20 blur-3xl"></div>

        <div class="mx-auto w-full max-w-7xl px-4 py-24">
            <div class="max-w-3xl">
                <span data-reveal class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm font-medium backdrop-blur">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-teal-300 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-teal-400"></span>
                    </span>
                    {{ __('website.home.hero_badge') }}
                </span>

                <h1 data-reveal style="--reveal-delay: 80ms" class="mt-6 text-4xl font-black leading-[1.05] tracking-tight drop-shadow-sm sm:text-6xl lg:text-7xl">
                    {{ __('website.home.hero_title') }}
                </h1>

                <p data-reveal style="--reveal-delay: 160ms" class="mt-6 max-w-xl text-lg text-slate-100/90 sm:text-xl">
                    {{ __('website.home.hero_copy') }}
                </p>

                <div data-reveal style="--reveal-delay: 240ms" class="mt-9 flex flex-wrap items-center gap-4">
                    <a href="{{ route('booking.create', ['locale' => app()->getLocale()]) }}"
                       class="group inline-flex items-center gap-2 rounded-full bg-teal-500 px-7 py-3.5 text-base font-semibold text-white shadow-lg shadow-teal-900/40 transition hover:bg-teal-400 hover:shadow-xl hover:shadow-teal-900/50">
                        {{ __('website.book_now') }}
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="{{ route('destinations.index', app()->getLocale()) }}"
                       class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/5 px-7 py-3.5 text-base font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        {{ __('website.nav.destinations') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Layered SVG waves along the foot of the hero, the front one drifting
             forever. The container is 200% wide so the -50% loop is seamless. --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-24 overflow-hidden sm:h-32">
            <svg class="wave-drift h-full w-[200%]" viewBox="0 0 2880 120" preserveAspectRatio="none" aria-hidden="true">
                <path fill="#ffffff" fill-opacity="0.12" d="M0 60c240 40 480 40 720 20s480-60 720-60 480 40 720 60 480 20 720 0v40H0z"/>
            </svg>
            <svg class="wave-drift absolute inset-x-0 bottom-0 h-full w-[200%]" style="animation-duration: 12s" viewBox="0 0 2880 120" preserveAspectRatio="none" aria-hidden="true">
                <path fill="#f8fafc" d="M0 80c240 30 480 30 720 10s480-40 720-40 480 30 720 40 480 10 720-10v40H0z"/>
            </svg>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12">
        <x-public.section-heading :title="__('website.home.destination_selector')" />
        <div data-reveal-group class="grid gap-4 md:grid-cols-2">
            @foreach ($destinations as $destination)
                <x-public.destination-card :destination="$destination" />
            @endforeach
        </div>
    </section>

    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4">
            <x-public.section-heading :title="__('website.home.featured_tours')" :copy="__('website.home.meta_description')" />
            <div data-reveal-group class="grid gap-5 lg:grid-cols-3">
                @foreach ($featuredTours as $tour)
                    <x-public.tour-card :tour="$tour" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-public.section-heading :title="__('website.home.categories')" />
            <div data-reveal-group class="grid gap-4 md:grid-cols-3">
                @foreach ($categories as $category)
                    <x-public.category-card :category="$category" />
                @endforeach
            </div>
        </div>
        <aside class="rounded-lg bg-teal-800 p-6 text-white">
            <h2 class="text-2xl font-bold">{{ __('website.home.contact_cta') }}</h2>
            <p class="mt-3 text-teal-50">{{ __('website.booking.no_payment_notice') }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <x-public.button :href="route('booking.create', ['locale' => app()->getLocale()])" variant="secondary">
                    {{ __('website.book_now') }}
                </x-public.button>
                <a href="{{ route('contact', app()->getLocale()) }}" class="inline-flex items-center text-sm font-semibold text-teal-100 underline-offset-4 hover:underline">
                    {{ __('website.nav.contact') }}
                </a>
            </div>
        </aside>
    </section>

    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4">
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ([__('website.home.why_choose_us'), __('website.home.video'), __('website.home.instagram')] as $heading)
                    <div class="rounded-lg border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-950">{{ $heading }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ __('website.company_description') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($testimonials !== [])
        <section class="mx-auto max-w-7xl px-4 py-12">
            <x-public.section-heading :title="__('website.home.testimonials')" />
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($testimonials as $testimonial)
                    <blockquote class="rounded-lg bg-white p-6 shadow-sm">
                        @if ($testimonial['rating'])
                            <p class="text-sm text-amber-500" aria-label="{{ $testimonial['rating'] }}/5">
                                {{ str_repeat('★', $testimonial['rating']) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $testimonial['rating']) }}</span>
                            </p>
                        @endif
                        <p class="mt-2 text-slate-700">“{{ $testimonial['quote'] }}”</p>
                        <footer class="mt-3 text-sm">
                            <span class="font-semibold text-teal-800">{{ $testimonial['name'] }}</span>
                            @if ($testimonial['tour'])
                                <span class="text-slate-500"> · {{ $testimonial['tour'] }}</span>
                            @endif
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-slate-900 py-12 text-white">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 md:grid-cols-2">
            <div>
                <div class="flex items-baseline justify-between gap-4">
                    <h2 class="text-2xl font-bold">{{ __('website.home.latest_blog') }}</h2>
                    @if ($blogPosts !== [])
                        <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="text-sm font-semibold text-teal-300 hover:underline">
                            {{ __('website.blog.view_all') }}
                        </a>
                    @endif
                </div>
                @forelse ($blogPosts as $post)
                    <a href="{{ $post['url'] }}" class="mt-4 block rounded-lg border border-white/10 p-4 hover:bg-white/5">
                        <span class="font-semibold">{{ $post['title'] }}</span>
                        <span class="mt-2 block text-sm text-slate-300">{{ $post['excerpt'] }}</span>
                    </a>
                @empty
                    <p class="mt-4 text-sm text-slate-400">{{ __('website.blog.empty') }}</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('newsletter.subscribe', ['locale' => app()->getLocale()]) }}" class="rounded-lg bg-white p-6 text-slate-900">
                @csrf
                <h2 class="text-xl font-bold">{{ __('website.newsletter') }}</h2>
                @if (session('status'))
                    <p class="mt-3 text-sm font-medium text-teal-700">{{ session('status') }}</p>
                @endif
                @error('email')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <input type="email" name="email" value="{{ old('email') }}" class="mt-4 w-full rounded-md border-slate-300" placeholder="{{ __('website.email_placeholder') }}" required>
                <x-public.button class="mt-4" type="submit">{{ __('website.subscribe') }}</x-public.button>
            </form>
        </div>
    </section>
</x-layouts.public>
