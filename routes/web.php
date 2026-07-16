<?php

use App\Admin\ResourceSchema;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminResourceController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Auth\AuthPageController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Public\AccountController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DefaultLocaleRedirectController;
use App\Http\Controllers\Public\DestinationController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/', DefaultLocaleRedirectController::class)->name('default-locale');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthPageController::class, 'login'])->name('login');
    Route::post('/login', [AuthPageController::class, 'authenticate'])
        ->middleware('throttle:login')
        ->name('login.authenticate');

    Route::get('/forgot-password', [AuthPageController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [AuthPageController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::redirect('/admin/login', '/login')->name('admin.login.redirect');

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', [AuthPageController::class, 'verifyEmail'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/confirm-password', [AuthPageController::class, 'confirmPassword'])->name('password.confirm');
    Route::get('/profile', [AuthPageController::class, 'profile'])->name('profile.edit');
    Route::get('/change-password', [AuthPageController::class, 'changePassword'])->name('password.change');
    Route::post('/logout', [AuthPageController::class, 'logout'])->name('logout');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'active.user'])
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Settings is one form with one row per key — it has nothing to list,
        // create, or delete, so it gets its own controller instead of being
        // bent into the CRUD shape below.
        //
        // The route is named `.index` to match the sidebar, which builds every
        // link as route('admin.'.$link.'.index') — the page is the settings
        // form itself, since there is no list to index.
        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.index');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

        // `resource` is a route default rather than a URI segment, so Laravel
        // appends it *after* the URI parameters when it resolves controller
        // arguments — the bound order is [id, resource], not [resource, id].
        // Controller signatures must therefore take $id first on the {id}
        // routes; getting this backwards silently passes "10" as the resource
        // name and 404s every edit page.
        foreach (array_diff(ResourceSchema::RESOURCES, ['settings']) as $resource) {
            Route::get("/{$resource}", [AdminResourceController::class, 'index'])->defaults('resource', $resource)->name("{$resource}.index");
            Route::get("/{$resource}/create", [AdminResourceController::class, 'create'])->defaults('resource', $resource)->name("{$resource}.create");
            Route::post("/{$resource}", [AdminResourceController::class, 'store'])->defaults('resource', $resource)->name("{$resource}.store");
            Route::get("/{$resource}/{id}", [AdminResourceController::class, 'show'])->defaults('resource', $resource)->name("{$resource}.show");
            Route::get("/{$resource}/{id}/edit", [AdminResourceController::class, 'edit'])->defaults('resource', $resource)->name("{$resource}.edit");
            Route::put("/{$resource}/{id}", [AdminResourceController::class, 'update'])->defaults('resource', $resource)->name("{$resource}.update");
            Route::delete("/{$resource}/{id}", [AdminResourceController::class, 'destroy'])->defaults('resource', $resource)->name("{$resource}.destroy");
        }
    });

Route::prefix('{locale}')
    ->whereIn('locale', config('app.supported_locales', ['en', 'ar']))
    ->middleware('set.locale')
    ->group(function (): void {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
        Route::get('/tours', [TourController::class, 'index'])->name('tours.all');
        Route::get('/about', [PageController::class, 'about'])->name('pages.about');
        Route::get('/contact', [PageController::class, 'contact'])->name('contact');
        Route::post('/contact', [ContactController::class, 'send'])
            ->middleware('throttle:5,1')
            ->name('contact.send');
        Route::get('/faq', [PageController::class, 'faq'])->name('faq');

        // Declared before the /{destinationSlug} catch-all below, which would
        // otherwise swallow /blog and 404 it as a missing destination.
        Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/blog/{postSlug}', [BlogController::class, 'show'])->name('blog.show');

        Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
        Route::post('/book', [BookingController::class, 'store'])
            // Open to the internet with no login: rate limited so the bookings
            // table cannot be flooded from one address.
            ->middleware('throttle:6,1')
            ->name('booking.store');
        Route::get('/book/confirmed', [BookingController::class, 'confirmed'])->name('booking.confirmed');

        Route::middleware('auth')->prefix('account')->name('account.')->group(function (): void {
            Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
            Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
            Route::get('/bookings', [AccountController::class, 'bookings'])->name('bookings');
        });

        Route::get('/{destinationSlug}', [DestinationController::class, 'show'])->name('destinations.show');
        Route::get('/{destinationSlug}/tours', [TourController::class, 'index'])->name('tours.index');
        Route::get('/{destinationSlug}/tours/{tourSlug}', [TourController::class, 'show'])->name('tours.show');
    });
