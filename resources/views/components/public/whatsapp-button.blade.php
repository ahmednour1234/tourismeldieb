@props(['number' => null, 'label' => null])
@php($href = 'https://wa.me/'.preg_replace('/\D+/', '', (string) $number))
<a href="{{ $href }}" class="inline-flex items-center justify-center rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-green-700" target="_blank" rel="noopener">
    {{ $label ?? __('website.whatsapp') }}
</a>
