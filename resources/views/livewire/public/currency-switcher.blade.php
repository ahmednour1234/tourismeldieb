<div class="relative" x-data="{ open: false }">
    <button type="button" x-on:click="open = !open" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold">{{ $currency }}</button>
    <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute end-0 mt-3 w-48 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
        @foreach ($currencies as $item)
            <button type="button" wire:click="selectCurrency('{{ $item['code'] }}')" class="block w-full rounded-md px-3 py-2 text-start text-sm hover:bg-slate-50">
                {{ $item['symbol'] }} {{ $item['code'] }}
            </button>
        @endforeach
    </div>
</div>
