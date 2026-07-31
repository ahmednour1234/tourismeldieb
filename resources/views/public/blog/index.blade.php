<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-7xl px-4 py-12">
        <header class="max-w-2xl">
            <h1 class="text-3xl font-bold text-slate-950">{{ __('website.blog.title') }}</h1>
            <p class="mt-2 text-slate-600">{{ __('website.blog.subtitle') }}</p>
        </header>

        @forelse ($posts as $post)
            @if ($loop->first)
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @endif

            <article data-reveal class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/60">
                @if ($post['image'])
                    <img src="{{ $post['image'] }}" alt="" class="h-44 w-full object-cover" loading="lazy">
                @endif
                <div class="flex flex-1 flex-col p-5">
                    @if ($post['published_at'])
                        <time datetime="{{ $post['published_at']->toDateString() }}" class="text-xs text-slate-400">
                            {{ $post['published_at']->isoFormat('D MMMM YYYY') }}
                        </time>
                    @endif
                    <h2 class="mt-1 text-lg font-bold text-slate-950">
                        <a href="{{ $post['url'] }}" class="hover:text-teal-800">{{ $post['title'] }}</a>
                    </h2>
                    <p class="mt-2 flex-1 text-sm text-slate-600">{{ $post['excerpt'] }}</p>
                    <a href="{{ $post['url'] }}" class="mt-4 text-sm font-semibold text-teal-700 hover:underline">
                        {{ __('website.blog.read_more') }}
                    </a>
                </div>
            </article>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <p class="mt-8 rounded-lg border border-dashed border-slate-300 p-12 text-center text-slate-500">
                {{ __('website.blog.empty') }}
            </p>
        @endforelse
    </section>
</x-layouts.public>
