<section class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
    <div class="flex flex-wrap gap-2">
        @foreach ($steps as $number => $label)
            <button type="button" wire:click="$set('step', {{ $number }})" class="rounded-md px-3 py-2 text-xs font-semibold {{ $step === $number ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">{{ $number }}. {{ $label }}</button>
        @endforeach
    </div>
    <div class="mt-5 rounded-md bg-slate-50 p-4 dark:bg-slate-950">
        <h3 class="font-bold">{{ $steps[$step] }}</h3>
        @if ($step === 2)
            <x-forms.translation-tabs :languages="$languages">
                @foreach ($languages as $language)
                    <div x-show="tab === '{{ $language['code'] }}'" dir="{{ $language['direction'] }}" class="grid gap-3">
                        <x-forms.input name="wizard_{{ $language['code'] }}_name" wire:model="form.translations.{{ $language['code'] }}.name" :label="__('admin.forms.name')" :required="$language['code'] === 'en'" />
                        <x-forms.input name="wizard_{{ $language['code'] }}_slug" wire:model="form.translations.{{ $language['code'] }}.slug" label="Slug" :required="$language['code'] === 'en'" />
                        <x-forms.textarea name="wizard_{{ $language['code'] }}_description" wire:model="form.translations.{{ $language['code'] }}.description" :label="__('admin.forms.description')" :required="$language['code'] === 'en'" />
                    </div>
                @endforeach
            </x-forms.translation-tabs>
        @elseif ($step === 9)
            <livewire:admin.media.media-manager />
        @else
            <div class="grid gap-3 md:grid-cols-2">
                <x-forms.input name="wizard_code" wire:model="form.code" :label="__('admin.forms.code')" />
                <x-forms.textarea name="wizard_description" :label="$steps[$step]" />
            </div>
        @endif
    </div>
    <div class="mt-5 flex flex-wrap gap-3">
        <button type="button" wire:click="previous" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">{{ __('admin.actions.cancel') }}</button>
        <button type="button" wire:click="saveDraft" class="rounded-md border border-teal-700 px-4 py-2 text-sm font-semibold text-teal-800">{{ __('admin.tours.draft_saved') }}</button>
        <button type="button" wire:click="next" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white">{{ __('admin.actions.save_continue') }}</button>
        <button type="button" wire:click="publish" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white">{{ __('website.book_now') }}</button>
    </div>
</section>
