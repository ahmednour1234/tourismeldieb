<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ContactController
{
    public function send(Request $request, string $locale): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot: hidden from people, irresistible to bots.
            'website' => ['prohibited'],
        ], [], [
            'name' => __('website.forms.name'),
            'email' => __('website.forms.email'),
            'message' => __('website.forms.message'),
        ]);

        ContactMessage::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'locale' => app()->getLocale(),
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        return back()->with('status', __('website.contact.sent'));
    }
}
