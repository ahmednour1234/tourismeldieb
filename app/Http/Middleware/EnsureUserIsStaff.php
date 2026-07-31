<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps customers out of the admin area gracefully.
 *
 * A signed-in customer who follows a stray /admin link should land back on
 * their own account, not on a dead-end 403 they can neither understand nor act
 * on. Guests still get the normal login redirect from the `auth` middleware
 * that runs before this one.
 *
 * This is a coarse gate, not the authorization: individual resources are still
 * gated per-permission by the controller. It only decides "does this person
 * belong in /admin at all".
 */
final class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isStaff()) {
            return redirect()->route('account.dashboard', ['locale' => app()->getLocale()]);
        }

        return $next($request);
    }
}
