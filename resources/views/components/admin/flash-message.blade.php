@if (session('success'))
    <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300" role="status">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300" role="alert">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    {{-- The heading was previously __('validation.required', ['attribute' => 'form']),
         which rendered the literal "The form field is required." — a validation
         line masquerading as a heading. --}}
    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300" role="alert">
        <p class="font-semibold">{{ __('admin.crud.validation_failed') }}</p>
        <ul class="mt-2 list-disc ps-5">
            @foreach ($errors->unique() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
