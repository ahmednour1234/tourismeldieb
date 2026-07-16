<x-layouts.public :seo="$seo">
    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-16 lg:grid-cols-2">
        <div>
            <x-public.section-heading :title="__('website.nav.contact')" :copy="__('website.home.contact_cta')" />

            <dl class="mt-6 grid gap-4 text-sm">
                @if ($settings['phone'])
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('website.forms.phone') }}</dt>
                        <dd class="mt-1">
                            <a href="tel:{{ preg_replace('/[^+\d]/', '', $settings['phone']) }}" class="text-teal-700 hover:underline">{{ $settings['phone'] }}</a>
                        </dd>
                    </div>
                @endif
                @if ($settings['email'])
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('website.forms.email') }}</dt>
                        <dd class="mt-1">
                            <a href="mailto:{{ $settings['email'] }}" class="text-teal-700 hover:underline">{{ $settings['email'] }}</a>
                        </dd>
                    </div>
                @endif
                @if ($settings['address'])
                    <div>
                        <dt class="font-semibold text-slate-900">{{ __('website.contact.address') }}</dt>
                        <dd class="mt-1 text-slate-600">{{ $settings['address'] }}</dd>
                    </div>
                @endif
            </dl>

            <p class="mt-6 rounded-md border border-teal-100 bg-teal-50 p-4 text-sm text-teal-900">
                {{ __('website.contact.booking_hint') }}
                <a href="{{ route('booking.create', ['locale' => app()->getLocale()]) }}" class="font-semibold underline">
                    {{ __('website.book_now') }}
                </a>
            </p>
        </div>

        <div>
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->unique() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- This form previously had no method, no action and no @csrf, and
                 its button was type="button" — every message a visitor sent was
                 silently discarded. --}}
            <form method="POST" action="{{ route('contact.send', ['locale' => app()->getLocale()]) }}" class="rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div class="grid gap-4">
                    <x-forms.input
                        name="name"
                        :label="__('website.forms.name')"
                        :value="old('name', auth()->user()?->name)"
                        autocomplete="name"
                        required
                    />
                    <x-forms.input
                        name="email"
                        type="email"
                        :label="__('website.forms.email')"
                        :value="old('email', auth()->user()?->email)"
                        autocomplete="email"
                        required
                    />
                    <x-forms.textarea
                        name="message"
                        :label="__('website.forms.message')"
                        :value="old('message')"
                        required
                    />

                    {{-- Honeypot: named to look inviting to a bot and hidden
                         from people. A real visitor never fills this in. --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">{{ __('website.forms.leave_blank') }}</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <x-public.button type="submit">{{ __('website.forms.send') }}</x-public.button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.public>
