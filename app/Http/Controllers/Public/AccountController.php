<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Models\BookingRequest;
use App\Services\Support\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class AccountController
{
    public function __construct(
        private readonly SeoService $seoService,
    ) {}

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('public.account.dashboard', [
            'seo' => $this->seoService->page(['title' => __('website.account.dashboard'), 'robots' => 'noindex,nofollow']),
            'recentBookings' => $this->bookingsFor($request)->take(3),
            'bookingCount' => BookingRequest::query()->where('user_id', $user->id)->count(),
        ]);
    }

    public function bookings(Request $request): View
    {
        return view('public.account.bookings', [
            'seo' => $this->seoService->page(['title' => __('website.account.bookings'), 'robots' => 'noindex,nofollow']),
            'bookings' => $this->bookingsFor($request),
        ]);
    }

    public function wishlist(): View
    {
        return view('public.account.wishlist', ['seo' => $this->seoService->page(['title' => __('website.account.wishlist'), 'robots' => 'noindex,nofollow'])]);
    }

    public function profile(Request $request): View
    {
        return view('public.account.profile', [
            'seo' => $this->seoService->page(['title' => __('website.account.profile'), 'robots' => 'noindex,nofollow']),
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            // Optional: only touched when the customer wants a new password, and
            // only then is the current one required — proving they own the
            // account, not just that a session is open.
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ], [], [
            'name' => __('website.forms.name'),
            'email' => __('website.forms.email'),
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Changing the email un-verifies it: the new address has not been
        // proven to belong to them.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('status', __('website.account.saved'));
    }

    /**
     * A customer's own bookings, newest first — scoped to their id so the query
     * can never return anyone else's.
     *
     * @return Collection<int, BookingRequest>
     */
    private function bookingsFor(Request $request): Collection
    {
        return BookingRequest::query()
            ->with('tour.translation')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }
}
