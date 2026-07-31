<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class NewsletterController
{
    public function subscribe(Request $request, string $locale): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ], [], ['email' => __('website.forms.email')]);

        $email = Str::lower(trim($validated['email']));

        // Idempotent: re-subscribing the same address is not an error, and a
        // previously unsubscribed address is quietly resubscribed. The response
        // is the same either way, so it never reveals whether an address was
        // already on the list.
        NewsletterSubscription::query()->updateOrCreate(
            ['email' => $email],
            ['locale' => app()->getLocale(), 'unsubscribed_at' => null],
        );

        return back()->with('status', __('website.newsletter_thanks'));
    }
}
