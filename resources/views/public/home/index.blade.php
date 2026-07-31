<x-layouts.public :seo="$seo">
    <section class="relative overflow-hidden bg-slate-950 text-white">
        <img class="absolute inset-0 h-full w-full object-cover opacity-50" src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80" alt="">
        <div class="relative mx-auto grid min-h-[78vh] max-w-7xl content-center gap-8 px-4 py-20">
            <div class="max-w-3xl">
                <h1 class="text-4xl font-black tracking-normal sm:text-6xl">{{ __('website.home.hero_title') }}</h1>
                <p class="mt-5 text-lg text-slate-100">{{ __('website.home.hero_copy') }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-public.button :href="route('tours.all', app()->getLocale())">{{ __('website.book_now') }}</x-public.button>
                    <x-public.button :href="route('destinations.index', app()->getLocale())" variant="secondary">{{ __('website.nav.destinations') }}</x-public.button>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12">
        <x-public.section-heading :title="__('website.home.destination_selector')" />
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($destinations as $destination)
                <x-public.destination-card :destination="$destination" />
            @endforeach
        </div>
    </section>

    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4">
            <x-public.section-heading :title="__('website.home.featured_tours')" :copy="__('website.home.meta_description')" />
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($featuredTours as $tour)
                    <x-public.tour-card :tour="$tour" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-public.section-heading :title="__('website.home.categories')" />
            <div class="grid gap-4 md:grid-cols-3">
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
