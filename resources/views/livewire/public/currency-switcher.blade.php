<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
    <button
        type="button"
        x-on:click="open = ! open"
        class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold transition hover:border-teal-600"
    >
        {{ $currency }}
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top
        class="absolute end-0 z-50 mt-3 w-48 rounded-lg border border-slate-200 bg-white p-2 shadow-lg"
    >
        @foreach ($currencies as $item)
            {{-- click.outside used to sit on THIS dropdown div, so clicking a
                 button inside it counted as "outside" and Alpine closed the menu
                 before Livewire's wire:click could fire — the selection was
                 lost. It now lives on the parent wrapper above. --}}
            <button
                type="button"
                wire:click="selectCurrency('{{ $item['code'] }}')"
                @class([
                    'flex w-full items-center justify-between rounded-md px-3 py-2 text-sm transition hover:bg-slate-50',
                    'bg-teal-50 font-semibold text-teal-800' => $item['code'] === $currency,
                ])
            >
                <span>{{ $item['code'] }}</span>
                <span class="text-slate-400">{{ $item['symbol'] }}</span>
            </button>
        @endforeach
    </div>
</div>
