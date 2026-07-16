# Hurgada guide

A bilingual (English / Arabic, RTL) tourism platform for Red Sea day tours:
a public site, an admin dashboard, and a booking-request flow.

Built on Laravel 12, Blade + Livewire 4 + Alpine, Tailwind v4, PostgreSQL.

## Requirements

- PHP 8.2+ with `pdo_pgsql`
- PostgreSQL 14+
- Node 18+
- Composer

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Create the database first — the app does not create it for you.
createdb tourismeldieb          # or: CREATE DATABASE tourismeldieb;

php artisan migrate --seed
npm run build
php artisan serve
```

The seed creates a demo admin: `admin@hurgadaguide.example` / `password`.
It only works in `local` — see `AuthPageController::ensureDemoAdmin()`.

### If a page shows the wrong data, restart the server first

`php artisan serve` reads config **once at boot**. If you change `.env` — the
database especially — the running server keeps the old values, and the app will
happily serve a page built from the wrong database rather than fail. Restart
before debugging anything else:

```bash
php artisan config:clear && php artisan serve
```

## What runs where

| URL | What |
| --- | --- |
| `/{locale}` | Public site — `en`, `ar` |
| `/{locale}/tours`, `/{locale}/{destination}/tours/{tour}` | Catalogue |
| `/{locale}/book` | Booking request form |
| `/{locale}/blog` | Blog |
| `/login` | Sign in |
| `/admin` | Dashboard, catalogue, bookings, settings |

Locale is a URL prefix on public routes and is carried onto `/login` and
`/admin` from the session (`ApplySessionLocale`).

## Architecture

Every admin write follows one path, enforced by a test that forbids queries in
Blade:

```text
Blade -> Controller -> FormRequest -> DTO -> Action -> Service
      -> Repository interface -> Eloquent repository -> Model -> PostgreSQL
```

`app/Admin/ResourceSchema.php` is the single registry of what each admin
resource is: its model, fields, validation rules and translated fields. The
form, the validator and the repository all read from it, so a field cannot
render without also being validated.

Read `docs/architecture/batch-02.md` before making changes — it records the
design decisions and the 30 bugs fixed getting here. `batch-01.md` is the
original audit and is partly stale; batch-02 lists the corrections.

## Bookings

`/{locale}/book` records a **booking request** — no seat is held and no payment
is taken. An operator triages it at `/admin/bookings`. Capacity reservation and
payment are not built; the UI says so rather than implying a confirmed booking.

## Content

Nothing on the public site is hardcoded. Tours, destinations, categories, blog
posts, testimonials, languages, currencies and the company's contact details all
come from the database and are editable at `/admin`.

Images are per-row `image_url` fields. There are no stock-photo fallbacks: the
site previously hotlinked photo IDs on an external CDN, the host reassigned one,
and a picture of a man in a gym appeared on the Luxor temple tour. A missing
image renders a neutral placeholder — honest, unlike a confidently wrong one.

## Tests

```bash
php artisan test          # 111 tests
vendor/bin/pint           # code style
```

Tests run on SQLite in memory. PostgreSQL-only syntax will pass locally and fail
there — see `HasTranslations`, which uses a portable CASE ladder rather than
`DISTINCT ON` for exactly this reason.

## Before production

- **`MAIL_MAILER=log`** — booking confirmations go to `storage/logs`, not to
  customers. Set real mail credentials.
- **Email verification is not enforced.** `User` does not implement
  `MustVerifyEmail` while the `verified` middleware is on the admin group.
  Enable both together, with a working mailer.
- **Deleting a user or role is permanent** — those tables have no `deleted_at`.
