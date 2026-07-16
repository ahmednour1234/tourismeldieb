<?php

use App\Modules\Admin\Providers\AdminServiceProvider;
use App\Modules\Authentication\Providers\AuthenticationServiceProvider;
use App\Modules\Authorization\Providers\AuthorizationServiceProvider;
use App\Modules\Availability\Providers\AvailabilityServiceProvider;
use App\Modules\Booking\Providers\BookingServiceProvider;
use App\Modules\Content\Providers\ContentServiceProvider;
use App\Modules\Customer\Providers\CustomerServiceProvider;
use App\Modules\Destination\Providers\DestinationServiceProvider;
use App\Modules\Localization\Providers\LocalizationServiceProvider;
use App\Modules\Notification\Providers\NotificationServiceProvider;
use App\Modules\Payment\Providers\PaymentServiceProvider;
use App\Modules\Pricing\Providers\PricingServiceProvider;
use App\Modules\Review\Providers\ReviewServiceProvider;
use App\Modules\SEO\Providers\SEOServiceProvider;
use App\Modules\Settings\Providers\SettingsServiceProvider;
use App\Modules\Tour\Providers\TourServiceProvider;
use App\Modules\TourCategory\Providers\TourCategoryServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AuthenticationServiceProvider::class,
    AuthorizationServiceProvider::class,
    SettingsServiceProvider::class,
    LocalizationServiceProvider::class,
    SEOServiceProvider::class,
    ContentServiceProvider::class,
    DestinationServiceProvider::class,
    TourCategoryServiceProvider::class,
    PricingServiceProvider::class,
    TourServiceProvider::class,
    AvailabilityServiceProvider::class,
    CustomerServiceProvider::class,
    NotificationServiceProvider::class,
    PaymentServiceProvider::class,
    BookingServiceProvider::class,
    ReviewServiceProvider::class,
    AdminServiceProvider::class,
];
