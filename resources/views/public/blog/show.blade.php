<x-layouts.public :seo="$seo">
    <article class="mx-auto max-w-3xl px-4 py-12">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}" class="hover:text-teal-800">
                {{ __('website.blog.title') }}
            </a>
        </nav>

        <header class="mt-4">
            <h1 class="text-3xl font-bold leading-tight text-slate-950">{{ $post['title'] }}</h1>
            @if ($post['published_at'])
                <time datetime="{{ $post['published_at']->toDateString() }}" class="mt-2 block text-sm text-slate-400">
                    {{ $post['published_at']->isoFormat('D MMMM YYYY') }}
                </time>
            @endif
            @if ($post['excerpt'])
                <p class="mt-4 text-lg text-slate-600">{{ $post['excerpt'] }}</p>
            @endif
        </header>

        @if ($post['image'])
            <img src="{{ $post['image'] }}" alt="" class="mt-8 w-full rounded-lg object-cover">
        @endif

        {{-- The body is admin-authored plain text. It is escaped and rendered
             paragraph-by-paragraph rather than passed through {!! !!}: an admin
             account should not be able to inject script into a public page. --}}
        @if ($post['body'])
            <div class="prose prose-slate mt-8 max-w-none">
                @foreach (preg_split('/\R{2,}/', trim($post['body'])) as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endforeach
            </div>
        @endif

        @if ($posts !== [])
            <aside class="mt-16 border-t border-slate-200 pt-8">
                <h2 class="text-xl font-bold text-slate-950">{{ __('website.blog.more') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    @foreach ($posts as $other)
                        <a href="{{ $other['url'] }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-teal-700">
                            <span class="block font-semibold text-slate-800">{{ $other['title'] }}</span>
                            <span class="mt-1 block text-sm text-slate-500">{{ Str::limit($other['excerpt'], 70) }}</span>
                        </a>
                    @endforeach
                </div>
            </aside>
        @endif
    </article>
</x-layouts.public>
