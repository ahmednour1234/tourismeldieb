<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use Illuminate\Http\RedirectResponse;

final class DefaultLocaleRedirectController
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('home', ['locale' => session('locale', config('app.locale', 'en'))]);
    }
}
