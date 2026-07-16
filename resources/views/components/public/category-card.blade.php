@props(['category'])
<article class="rounded-lg border border-slate-200 bg-white p-5">
    <h3 class="font-bold text-slate-950">{{ $category['name'] }}</h3>
    <p class="mt-2 text-sm text-slate-600">{{ $category['description'] }}</p>
</article>
