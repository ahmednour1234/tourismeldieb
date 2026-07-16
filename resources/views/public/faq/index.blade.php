<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-4xl px-4 py-16">
        <x-public.section-heading :title="__('website.nav.faq')" />
        <div class="space-y-4">
            @foreach ([__('website.tours.cancellation'), __('website.tours.pickup'), __('website.booking_soon')] as $question)
                <details class="rounded-lg bg-white p-5 shadow-sm">
                    <summary class="cursor-pointer font-semibold text-slate-950">{{ $question }}</summary>
                    <p class="mt-3 text-slate-600">{{ __('website.company_description') }}</p>
                </details>
            @endforeach
        </div>
    </section>
</x-layouts.public>
