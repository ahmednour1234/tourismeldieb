<div class="rounded-md border border-dashed border-slate-300 p-5 dark:border-slate-700">
    <label class="block text-sm font-semibold">{{ __('admin.nav.media') }}</label>
    <input type="file" wire:model="uploads" multiple class="mt-3 block w-full text-sm">
    <div wire:loading wire:target="uploads" class="mt-3 text-sm text-teal-700">{{ __('website.search') }}...</div>
    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        @foreach ($uploads as $index => $upload)
            <div class="rounded-md bg-slate-100 p-3 text-sm dark:bg-slate-800">
                <p>{{ method_exists($upload, 'getClientOriginalName') ? $upload->getClientOriginalName() : __('admin.nav.media') }}</p>
                <button type="button" wire:click="remove({{ $index }})" class="mt-2 text-red-600">{{ __('admin.actions.delete') }}</button>
            </div>
        @endforeach
    </div>
</div>
