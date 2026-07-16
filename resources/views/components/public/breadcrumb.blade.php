@props(['items' => []])
<nav aria-label="Breadcrumb" class="text-sm text-slate-500">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $item)
            <li>
                @if (! empty($item['url']))
                    <a class="hover:text-teal-700" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-slate-800">{{ $item['label'] }}</span>
                @endif
            </li>
            @if (! $loop->last)
                <li aria-hidden="true">/</li>
            @endif
        @endforeach
    </ol>
</nav>
