# Phase 2 Batch 1 - Architecture Audit

> **Parts of this document are out of date. Read `batch-02.md` alongside it.**
>
> This was accurate when written, but the audit's own entry criteria have since
> been met and several findings no longer hold — the database exists and is
> fully migrated, the catalog tables and models are present, and the PHP version
> mismatch is resolved. `batch-02.md` lists the corrections and describes the
> system as it actually runs.
>
> The **Service Flow Contract** and **Planned Key Constraints** sections below
> remain authoritative and are implemented.

Batch 1 is an audit and architecture alignment pass for Phase 2: tour options,
availability, scheduling, pricing, currencies, coupons, admin interfaces, and the
public booking selector.

No new Laravel project was created. No database schema was changed in this
batch. Later batches must add migrations and code inside the existing Laravel
application and module system.

## Current Project Audit

### Runtime and Packages

| Area | Finding |
| --- | --- |
| Laravel | 12.64.0 from `php artisan about` |
| PHP runtime | 8.2.12 locally, while `composer.json` requires `^8.3` |
| Livewire | 4.3.3 |
| Tailwind | 4.0.0 via `@tailwindcss/vite` |
| Alpine | 3.15.12 |
| Database driver | `pgsql` in runtime config |
| Cache, queue, session | Redis in runtime config |
| Permissions | Spatie Laravel Permission 6.25.0 |
| Media/activity packages | Spatie Media Library and Activity Log are installed |

The local PostgreSQL database named `tourismeldieb` does not currently exist, so
`php artisan migrate:status` cannot inspect the live database. The migration
audit below is based on migration files in the repository.

### Existing Migrations and Tables

Current migration files create only framework and permission tables:

```text
users
password_reset_tokens
sessions
cache
cache_locks
jobs
job_batches
failed_jobs
permissions
roles
model_has_permissions
model_has_roles
role_has_permissions
```

There are no repository migrations for:

```text
languages
currencies
countries
destinations
tour_categories
tours
tour_translations
tour_options
tour_schedules
tour_departures
tour_blackout_dates
tour_prices
pricing_rules
coupons
currency_rates
```

The existing public destination, category, and tour data is served from
`App\Services\Public\PublicPageService` arrays. The existing admin CRUD pages are
Blade placeholders behind `AdminResourceController`.

### Database Capabilities

Phase 2 should target PostgreSQL first.

PostgreSQL supports the constraints Phase 2 needs:

```text
CHECK constraints for positive capacities, quantities, and money amounts
foreign keys with restricted deletes where history must be preserved
partial unique indexes for one default option per tour
partial unique indexes for active/deleted-aware uniqueness
range overlap protection through exclusion constraints when needed
row-level locking through SELECT ... FOR UPDATE for capacity reservations
JSONB columns for days-of-week rule storage if normalized pivots are not chosen
```

SQLite in-memory tests remain useful for Blade smoke tests and simple service
tests, but they must not be the only proof for PostgreSQL row locking,
exclusion/range constraints, or concurrency behavior.

### Existing Module System

The app already has a module provider pattern:

```text
App\Shared\Services\ModuleServiceProvider
App\Shared\Contracts\ModuleServiceProviderContract
bootstrap/providers.php
config/modules.php
```

Registered modules:

```text
Authentication
Authorization
Settings
Localization
SEO
Content
Destination
TourCategory
Pricing
Tour
Availability
Customer
Notification
Payment
Booking
Review
Admin
```

Most modules currently contain only a service provider. The provider base class
already supports module migrations, routes, views, translations, and repository
bindings when files are added later.

### Tour Module

Current `TourServiceProvider` dependencies:

```text
Destination
TourCategory
Pricing
SEO
```

There is no `Tour` model, `tours` table, repository interface, Eloquent
repository, policy, action, DTO, or form request in the repository yet.

The public tour routes are already present:

```text
GET /{locale}/tours
GET /{locale}/{destinationSlug}/tours
GET /{locale}/{destinationSlug}/tours/{tourSlug}
```

The admin route loop includes `tours`, but the controller returns static
resource Blade pages and does not persist tour data.

### Current Tour Wizard

The current Livewire wizard is:

```text
App\Livewire\Admin\Tours\TourWizard
resources/views/livewire/admin/tours/tour-wizard.blade.php
```

It has 12 presentation steps and stores no tour data. It validates only basic
placeholder fields and flashes placeholder success messages. Phase 2 must keep
the component as presentation state only and route persistence through DTOs,
actions, services, and repositories.

### Current Public Tour Page

The public tour page is:

```text
resources/views/public/tours/show.blade.php
```

It displays array-backed tour details and a booking placeholder:

```text
price_soon
booking_soon
```

Phase 2 must replace that placeholder with a Livewire selector that calls
availability, pricing, coupon, and currency services. Blade must still contain
no direct model or query access.

### Current Currency Handling

Currency UI is currently:

```text
App\Livewire\Public\CurrencySwitcher
App\Services\Support\UiSettingsService::activeCurrencies()
```

Currencies are hardcoded as USD, EUR, and EGP in a support service. There is no
`currencies` table, `currency_rates` table, repository, or conversion service
yet.

### Current Money Object

An existing immutable value object is present:

```text
App\Shared\Money\Money
```

It currently supports:

```text
minor amount
ISO currency code validation
zero()
add()
subtract()
currency mismatch protection through InvalidArgumentException
```

Phase 2 should extend or replace it conservatively to support multiplication,
percentage adjustment, comparisons, deterministic rounding support, and a
domain-specific `CurrencyMismatchException`.

### Permissions and Policies

Spatie Permission is installed and admin views currently gate resource access
with permissions like:

```text
tours.view
currencies.view
roles.view
```

There are no domain policies for tour options, schedules, departures, blackout
dates, prices, rules, coupons, or currency rates yet.

### Tests

Current tests cover:

```text
public Blade page smoke tests
admin Blade permission smoke tests
static Blade scan forbidding direct database query patterns
example unit and feature tests
```

`phpunit.xml` uses SQLite in memory, array cache, sync queue, and array session.
Phase 2 must add PostgreSQL-backed tests for locking/concurrency.

## Phase 2 Module Boundary

Phase 2 should keep these modules focused:

```text
Tour
  Owns the core tour aggregate and publishing readiness.

TourOption
  Owns bookable options/packages and option translations.

Availability
  Owns schedules, departures, blackout dates, availability reads, and capacity
  reservation.

Pricing
  Owns base prices, pricing rules, pricing engine, money math, currency rates,
  and conversion.

Coupon
  Owns coupon definitions, validation, applicability, and usage foundations.
```

The existing `Booking`, `Payment`, `Customer`, `Notification`, and `Review`
modules must remain foundations only for this phase. Phase 2 must not create
real customer bookings or payment processing.

## Phase 2 Module Dependencies

Planned dependency direction:

```text
Settings
  -> Localization
  -> SEO

Destination + TourCategory + SEO
  -> Tour

Tour + Localization + Authorization
  -> TourOption

Tour + TourOption
  -> Availability

Settings + Tour + TourOption
  -> Pricing

Pricing + Tour + TourOption
  -> Coupon

Availability + Pricing + Coupon + Currency services
  -> Public booking selector

TourOption + Availability + Pricing + Coupon
  -> Admin Phase 2 interfaces
```

Repository bindings should be registered in each module provider as concrete
repositories are introduced. Do not bind interfaces before the implementation
class exists.

## Required Database Changes For Later Batches

Batch 2 should add migrations in this dependency order:

```text
1. Core catalog persistence required by current placeholders
   languages
   currencies
   countries
   destinations
   destination_translations
   tour_categories
   tour_category_translations
   tours
   tour_translations

2. Tour options
   tour_options
   tour_option_translations

3. Availability
   tour_schedules
   tour_departures
   tour_blackout_dates

4. Pricing and currency
   tour_prices
   pricing_rules
   currency_rates

5. Coupons
   coupons
   coupon_tour
   coupon_tour_option

6. Activity/media/package support, if not already published
   media
   activity_log
```

Phase 2 booking-selection storage should use a signed token or session payload.
It must not add customer booking rows yet.

## Planned Key Constraints

### Tour Options

```text
unique(tour_id, code)
unique(tour_option_id, locale)
unique(tour_id, locale, slug) through translation design or a PostgreSQL index
CHECK capacity > 0
CHECK minimum_guests >= 1
CHECK maximum_guests >= minimum_guests
CHECK minimum_booking_quantity >= 1
CHECK maximum_booking_quantity <= capacity
partial unique index for one default option per tour where is_default = true
```

### Schedules

```text
CHECK capacity_override IS NULL OR capacity_override > 0
CHECK booking_cutoff_hours >= 0
CHECK valid_to IS NULL OR valid_to >= valid_from
overlap prevention for option + day + start_time + valid date range
```

PostgreSQL exclusion constraints are preferred for robust overlap prevention.
The service validator must still exist for user-friendly validation errors.

### Departures

```text
unique(tour_option_id, start_datetime)
CHECK capacity > 0
CHECK reserved_capacity >= 0
CHECK confirmed_capacity >= 0
CHECK reserved_capacity + confirmed_capacity <= capacity
available_capacity must be calculated or database-protected from going negative
```

Capacity reservation must use transactions and repository-level `lockForUpdate`.

### Blackout Dates

```text
CHECK end_date >= start_date
index(tour_id, start_date, end_date)
index(tour_option_id, start_date, end_date)
```

Cancelling existing departures must be explicit and evented.

### Prices and Rules

```text
tour_prices.amount_minor >= 0
tour_prices valid_to IS NULL OR valid_to >= valid_from
overlap prevention for option + guest_type + currency + valid range
pricing_rules ends_at IS NULL OR ends_at >= starts_at
pricing_rules minimum_guests IS NULL OR maximum_guests IS NULL OR minimum_guests <= maximum_guests
pricing_rules minimum_days_before IS NULL OR maximum_days_before IS NULL OR minimum_days_before <= maximum_days_before
fixed adjustments require currency_id
percentage adjustments do not require currency_id
```

All money must be stored as integer minor units.

### Coupons and Currency Rates

```text
coupons.code unique, normalized uppercase
coupons expires_at IS NULL OR expires_at >= starts_at
fixed coupon discounts require currency_id
currency_rates unique(base_currency_id, target_currency_id, effective_at)
currency_rates rate stored as fixed precision decimal/string, not float
```

## Service Flow Contract

Every write path added in Phase 2 must follow this flow:

```text
Blade / Livewire
  -> Controller or Livewire component
  -> Form Request or Livewire validation
  -> DTO
  -> Action
  -> Service
  -> Repository Interface
  -> Eloquent Repository
  -> Eloquent Model
  -> PostgreSQL
```

Rules:

```text
Controllers and Livewire components are presentation-layer only.
Repositories own database access.
Services own business rules.
Actions orchestrate use cases and transaction boundaries when needed.
Blade files must not contain direct model queries.
Money calculations must stay inside Pricing services/value objects.
Availability and capacity decisions must never trust browser values.
```

## Batch 1 Decisions

1. Keep the existing module provider base class and use it for Phase 2 modules.
2. Add real files only when each batch implements a class or artifact.
3. Keep current public routes and Blade smoke tests working while replacing
   array-backed data gradually.
4. Introduce `TourOption` and `Coupon` modules when their first concrete
   migration/provider/repository files are added.
5. Keep `Availability` for schedules, departures, blackout dates, calendar
   reads, and capacity reservations.
6. Keep `Pricing` for prices, rules, currency rates, conversion, and the engine.
7. Preserve the existing `Money` namespace and extend behavior in place unless
   tests prove a clean replacement is safer.
8. Use PostgreSQL-specific constraints where Phase 2 requires correctness.
9. Maintain SQLite tests for fast smoke coverage, but add PostgreSQL test paths
   for lock/concurrency behavior.

## Batch 1 Verification

Commands run during this audit:

```bash
php artisan about
php artisan route:list --except-vendor
php artisan schedule:list
php artisan migrate:status
```

Results:

```text
about: succeeded
route:list: succeeded, 86 application routes
schedule:list: succeeded, no scheduled tasks defined
migrate:status: failed because PostgreSQL database "tourismeldieb" does not exist
```

## Next Batch Entry Criteria

Before Batch 2 starts:

```text
Create or configure the PostgreSQL database used by .env.
Resolve the local PHP 8.2.12 versus composer ^8.3 mismatch.
Keep the existing route and Blade tests passing.
Add migrations in dependency order.
Prefer PostgreSQL-safe constraints with SQLite-compatible fallbacks only where
tests require them.
```
