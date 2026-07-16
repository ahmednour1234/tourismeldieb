@props(['for', 'required' => false])
<label for="{{ $for }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
    {{ $slot }} @if ($required)<span class="text-red-600">*</span>@endif
</label>
