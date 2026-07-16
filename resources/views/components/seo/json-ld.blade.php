@props(['data' => []])
@if ($data !== [])
    <script type="application/ld+json">@json($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
@endif
