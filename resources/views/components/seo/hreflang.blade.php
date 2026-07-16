@props(['items' => []])
@foreach ($items as $item)
    <link rel="alternate" hreflang="{{ $item['locale'] }}" href="{{ $item['url'] }}">
@endforeach
