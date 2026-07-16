<x-layouts.public :seo="$seo">
    @php
        $stats = __('website.about.stats');
        $values = __('website.about.values');
        $experiences = __('website.about.experiences');
        $process = __('website.about.process');
    @endphp

    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-16">
            <div>
                <p class="text-sm font-bold uppercase tracking-normal text-teal-700">{{ __('website.about.eyebrow') }}</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black leading-tight tracking-normal text-slate-950 sm:text-5xl lg:text-6xl">
                    {{ __('website.about.title') }}
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                    {{ __('website.about.intro') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-public.button :href="route('tours.all', app()->getLocale())">{{ __('website.book_now') }}</x-public.button>
                    <x-public.button :href="route('contact', app()->getLocale())" variant="secondary">{{ __('website.nav.contact') }}</x-public.button>
                </div>
            </div>

            <div class="relative">
                <img
                    class="aspect-[4/3] w-full rounded-lg object-cover shadow-xl"
                    src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1400&q=85"
                    alt="{{ __('website.about.hero_badge') }}"
                >
                <div class="absolute inset-x-4 bottom-4 rounded-lg bg-white/95 p-4 shadow-lg ring-1 ring-slate-900/10 backdrop-blur">
                    <p class="text-sm font-bold text-teal-800">{{ __('website.about.hero_badge') }}</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ __('website.about.hero_note') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 py-8 sm:grid-cols-3">
            @foreach ($stats as $stat)
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-3xl font-black text-teal-800">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div>
                <x-public.section-heading :title="__('website.about.story_title')" />
            </div>
            <div class="space-y-6 text-lg leading-8 text-slate-600">
                <p>{{ __('website.about.story_copy') }}</p>
                <p>{{ __('website.company_description') }}</p>
            </div>
        </div>
    </section>

    <section class="bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-normal text-teal-300">{{ __('website.home.why_choose_us') }}</p>
                <h2 class="mt-3 text-3xl font-black tracking-normal sm:text-4xl">{{ __('website.about.values_title') }}</h2>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ($values as $value)
                    <article class="rounded-lg border border-white/10 bg-white/5 p-6">
                        <h3 class="text-xl font-bold">{{ $value['title'] }}</h3>
                        <p class="mt-3 leading-7 text-slate-300">{{ $value['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <x-public.section-heading :title="__('website.about.experiences_title')" :copy="__('website.home.hero_copy')" />

            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($experiences as $index => $experience)
                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <img
                            class="aspect-[16/10] w-full object-cover"
                            src="{{ [
                                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                                'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                                'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=900&q=80',
                            ][$index] }}"
                            alt="{{ $experience['title'] }}"
                        >
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-slate-950">{{ $experience['title'] }}</h3>
                            <p class="mt-3 leading-7 text-slate-600">{{ $experience['copy'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-[0.85fr_1.15fr]">
            <div>
                <x-public.section-heading :title="__('website.about.process_title')" :copy="__('website.about.hero_note')" />
            </div>

            <div class="grid gap-4">
                @foreach ($process as $item)
                    <article class="grid gap-4 rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:grid-cols-[4rem_1fr]">
                        <p class="text-2xl font-black text-teal-700">{{ $item['step'] }}</p>
                        <div>
                            <h3 class="text-lg font-bold text-slate-950">{{ $item['title'] }}</h3>
                            <p class="mt-2 leading-7 text-slate-600">{{ $item['copy'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <div class="grid gap-8 rounded-lg bg-teal-800 p-8 text-white shadow-xl md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="text-3xl font-black tracking-normal">{{ __('website.about.cta_title') }}</h2>
                    <p class="mt-3 max-w-2xl leading-7 text-teal-50">{{ __('website.about.cta_copy') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-teal-900 transition hover:bg-teal-50" href="{{ route('tours.all', app()->getLocale()) }}">
                        {{ __('website.tours.title') }}
                    </a>
                    <a class="inline-flex items-center justify-center rounded-md border border-white/70 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10" href="{{ route('contact', app()->getLocale()) }}">
                        {{ __('website.nav.contact') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
