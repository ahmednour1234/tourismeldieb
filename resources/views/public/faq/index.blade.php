<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-4xl px-4 py-16">
        <x-public.section-heading :title="__('website.nav.faq')" />

        {{-- This page used to render three headings — one of them literally
             "Booking will be available soon" — each answered with the company
             description. It now shows the policies an admin has actually
             written, and says so plainly when none have been. --}}
        @php
            $entries = array_filter([
                __('website.tours.cancellation') => $settings['policy_cancellation'] ?? '',
                __('website.tours.important') => $settings['policy_important'] ?? '',
            ], static fn (string $answer): bool => $answer !== '');
        @endphp

        @if ($entries === [])
            <p class="rounded-lg bg-white p-6 text-slate-600 shadow-sm">
                {{ __('website.faq_empty', ['email' => $settings['email']]) }}
            </p>
        @else
            <div class="space-y-4">
                @foreach ($entries as $question => $answer)
                    <details class="rounded-lg bg-white p-5 shadow-sm">
                        <summary class="cursor-pointer font-semibold text-slate-950">{{ $question }}</summary>
                        <p class="mt-3 whitespace-pre-line text-slate-600">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
