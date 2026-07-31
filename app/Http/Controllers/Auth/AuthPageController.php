<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Admin\ResourceSchema;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class AuthPageController
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (app()->environment('local') && $credentials['email'] === 'admin@hurgadaguide.example') {
            $this->ensureDemoAdmin();
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()));
    }

    /**
     * The dedicated admin sign-in page, styled for staff rather than customers.
     */
    public function adminLogin(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Authenticate specifically into the admin.
     *
     * Unlike the public login, this rejects a valid customer account: signing
     * in here must land on the dashboard, so a non-staff user is refused rather
     * than silently bounced to their account.
     */
    public function adminAuthenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (app()->environment('local') && $credentials['email'] === 'admin@hurgadaguide.example') {
            $this->ensureDemoAdmin();
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        // Credentials were valid, but this door is staff only. A customer who
        // guessed their way here is logged straight back out with a clear
        // message rather than dumped on a 403 inside /admin.
        if (! $request->user()?->isStaff()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => __('auth.not_staff')])
                ->onlyInput('email');
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Where a freshly authenticated user belongs.
     *
     * Staff go to the dashboard; a customer goes to their account. Sending a
     * customer to /admin — as the login used to, unconditionally — dropped them
     * on a 403 with no way forward.
     */
    private function homeFor(?User $user): string
    {
        if ($user?->isStaff()) {
            return route('admin.dashboard');
        }

        return route('account.dashboard', ['locale' => app()->getLocale()]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }

    private function ensureDemoAdmin(): void
    {
        $user = User::query()->firstOrNew(['email' => 'admin@hurgadaguide.example']);
        $user->forceFill([
            'name' => 'Demo Admin',
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => Hash::make('password'),
        ])->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Grant the full ability set via the `admin` role rather than a
        // hand-maintained permission list, so the demo account never drifts
        // out of sync with PermissionSeeder.
        foreach (ResourceSchema::RESOURCES as $resource) {
            foreach (['view', 'create', 'update', 'delete'] as $ability) {
                Permission::findOrCreate($resource.'.'.$ability, 'web');
            }
        }

        $role = Role::findOrCreate('admin', 'web');
        $role->syncPermissions(Permission::query()->pluck('name')->all());
        $user->syncRoles([$role->name]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function forgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function resetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->string('email'),
        ]);
    }

    public function verifyEmail(): View
    {
        return view('auth.verify-email');
    }

    public function confirmPassword(): View
    {
        return view('auth.confirm-password');
    }

    public function profile(): View
    {
        return view('auth.profile');
    }

    public function changePassword(): View
    {
        return view('auth.change-password');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
