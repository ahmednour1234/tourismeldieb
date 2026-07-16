<div class="grid gap-6 lg:grid-cols-[300px_1fr]">
    <aside x-data="{ open: false }" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <button type="button" x-on:click="open = !open" class="mb-4 w-full rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold lg:hidden">{{ __('website.tours.filters') }}</button>
        <div x-bind:class="open ? 'block' : 'hidden lg:block'" class="space-y-4">
            <input wire:model.live.debounce.300ms="search" type="search" class="w-full rounded-md border-slate-300" placeholder="{{ __('website.search') }}">
            <select wire:model.live="destination" class="w-full rounded-md border-slate-300">
                <option value="">{{ __('website.nav.destinations') }}</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination['slug'] }}">{{ $destination['name'] }}</option>
                @endforeach
            </select>
            <select wire:model.live="category" class="w-full rounded-md border-slate-300">
                <option value="">{{ __('website.nav.categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>
                @endforeach
            </select>
            <select wire:model.live="tourType" class="w-full rounded-md border-slate-300">
                <option value="">{{ __('website.tours.private') }} / {{ __('website.tours.shared') }}</option>
                <option value="private">{{ __('website.tours.private') }}</option>
                <option value="shared">{{ __('website.tours.shared') }}</option>
            </select>
            <label class="flex items-center gap-2 text-sm"><input wire:model.live="featured" type="checkbox" class="rounded text-teal-700"> {{ __('website.tours.featured') }}</label>
            <label class="flex items-center gap-2 text-sm"><input wire:model.live="bestSeller" type="checkbox" class="rounded text-teal-700"> {{ __('website.tours.best_seller') }}</label>
            <button type="button" wire:click="clearFilters" class="text-sm font-semibold text-teal-700">{{ __('website.clear_filters') }}</button>
        </div>
    </aside>
    <section>
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-slate-500">{{ count($tours) }} {{ __('website.tours.title') }}</p>
            <select wire:model.live="sort" class="rounded-md border-slate-300">
                <option value="recommended">{{ __('website.tours.recommended') }}</option>
                <option value="name">{{ __('website.tours.name') }}</option>
                <option value="duration">{{ __('website.tours.duration') }}</option>
            </select>
        </div>
        <div wire:loading>
            <x-public.loading-state />
        </div>
        <div wire:loading.remove class="grid gap-5 xl:grid-cols-2">
            @forelse ($tours as $tour)
                <x-public.tour-card :tour="$tour" />
            @empty
                <x-public.empty-state :title="__('website.tours.empty')" />
            @endforelse
        </div>
    </section>
</div>
