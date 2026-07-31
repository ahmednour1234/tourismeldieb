@props(['category'])
<article data-reveal class="rounded-xl border border-slate-200 bg-white p-5 transition duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-lg hover:shadow-slate-200/60">
    <h3 class="font-bold text-slate-950">{{ $category['name'] }}</h3>
    <p class="mt-2 text-sm text-slate-600">{{ $category['description'] }}</p>
</article>
