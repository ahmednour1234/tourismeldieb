# Phase 2 Batch 2 - Admin Write Path

Batch 2 implements the write path that `batch-01.md` specified: real persistence
for the nine admin resources, a working dashboard, and the auth gaps closed.

**`batch-01.md` is now partly stale.** Read this file alongside it. Where they
disagree, this one describes the running system. The specific corrections:

| batch-01 says | Reality |
| --- | --- |
| The `tourismeldieb` database does not exist | It exists, and all migrations have run |
| No repository migrations for `languages`…`currency_rates` | All present since `2026_07_16_210000` / `210100` |
| PHP 8.2.12 vs composer `^8.3` mismatch is an entry criterion | Resolved: `composer.json` now requires `^8.2`; no dependency needs 8.3 |
| There is no `Tour` model / repository / DTO | All exist — see below |

## Batch 1 Entry Criteria: Status

```text
Create or configure the PostgreSQL database        DONE (it already existed)
Resolve PHP 8.2.12 versus composer ^8.3            DONE (relaxed to ^8.2)
Keep the existing route and Blade tests passing    DONE (61 tests pass)
Add migrations in dependency order                 DONE (already present)
```

## What Batch 2 Added

### The write path

The flow `batch-01.md` mandated is now real for every admin resource:

```text
Blade
  -> AdminResourceController          presentation only; no queries, no rules
  -> ResourceRequest                  rules derived from ResourceSchema
  -> ResourceData                     readonly DTO
  -> Store/Update/DeleteResourceAction  owns the transaction boundary
  -> ResourceService                  business rules + activity log
  -> ResourceRepositoryContract
  -> EloquentResourceRepository       owns all database access
  -> Model -> PostgreSQL
```

Bound in `App\Modules\Admin\Providers\AdminServiceProvider::repositoryBindings()`.

### `App\Admin\ResourceSchema`

One registry describing each resource: its model, its fields, its validation
rules, its translated fields. The form, the validator, and the repository all
read from it, so a field cannot render without also being validated.

This replaced a single hardcoded `name` / `code` / `status` / `active` form that
was shown for all nine resources and matched almost none of the real tables.

### Models

`Language`, `Currency`, `Country`, `Destination(+Translation)`,
`TourCategory(+Translation)`, `Tour(+Translation)`, plus
`App\Models\Concerns\HasTranslations`.

`HasTranslations::translation()` is a real eager-loadable `HasOne` that resolves
the active locale with a fallback. It is written with a portable CASE ladder:
PostgreSQL's `DISTINCT ON` / `array_position()` are hard syntax errors on the
SQLite test connection, and `ofMany()` cannot express locale *preference*
because it can only aggregate real columns.

### Settings

`/admin/settings` is a real form. It was the last screen that admitted it was
not built.

```text
settings table          key/value, one row per key, JSON value
App\Admin\SettingSchema declares each setting: group, control, rules, default
AdminSettingController  its own controller — one form, nothing to list
SettingRequest -> UpdateSettingsAction -> SettingService
  -> SettingRepositoryContract -> EloquentSettingRepository
```

Key/value rather than a column per setting, because settings arrive in batches
and a column-based table needs a migration for every new field. `value` is JSON
so a setting can hold a scalar, a per-locale map, or a nested structure.

The whole table is cached as one entry (`Cache::rememberForever`); every write
busts it. There is a test proving the bust, because without it the public site
would show stale values indefinitely.

**This moved real data out of hardcoded PHP.** `UiSettingsService` previously
returned a hardcoded phone number, address, and social links, and a hardcoded
`['en', 'ar']` language list that ignored the `languages` table (which has
Russian in it). It now reads settings and the catalogue.

The activity log records *which keys* changed, never their values: these are
public marketing details today, but this table is the natural home for an API
key later, and an audit log is a poor place to leak one.

### Blog and testimonials — the public site now runs entirely on the database

`blog_posts` and `testimonials` (each with a `*_translations` sibling) were the
last public content with no tables, so `PublicPageService` returned hardcoded
arrays for them. Both are now full resources: admin CRUD, bilingual, permissions,
activity logging, and public pages at `/{locale}/blog` and `/{locale}/blog/{slug}`.
The nav's "Blog" link was an `href="#"`.

**All fallback arrays are gone.** `PublicPageService` used to try the database
and, on any miss, return ~200 lines of invented destinations, tours and
categories — complete with fake tour counts and Unsplash images. That is
precisely how a server pointed at the wrong database rendered a plausible,
entirely fictional site without anyone noticing. It now reads the catalogue or
returns nothing.

Two things fell out of removing them:

- `destinations()` ran a `count()` **per row** for its tour count; it is now one
  aggregate subquery.
- The methods joined translations on the active locale with an **inner join**, so
  a row without an Arabic translation vanished from the Arabic site entirely.
  They now use the fallback-aware `translation` relation: the row renders with
  its English copy instead of disappearing.

### Booking requests — "Book Now" now does something

"Book Now" was a `type="button"` wired to nothing, on the header, every tour
card, and the tour page. The only other route to the business was a Contact form
that had **no method, no action, no CSRF token and its own `type="button"`** — so
every enquiry any visitor ever made was silently discarded.

```text
booking_requests   reference, tour, customer, date, guest counts, status
contact_messages   name, email, message, ip, status
BookingRequestForm -> SubmitBookingRequestAction -> BookingRequestService
  -> BookingRequest -> PostgreSQL, then the confirmation email
```

Deliberately a **request queue, not a reservation system**: no seat is held and
no money is taken. Capacity locking and payment belong to a later batch, and
pretending to hold a seat we have not reserved would be worse than not holding
one. The UI says so plainly rather than implying a confirmed booking.

Decisions worth keeping:

- **The tour id is never trusted.** It is re-resolved against `published()`, so a
  draft or archived tour cannot be booked by guessing a sequential id.
- **The reference travels in the session, not the URL.** A shareable
  `/book/confirmed?ref=…` would let anyone read a stranger's name, email and
  phone.
- **Mail is sent after the transaction commits.** A mail failure must not roll
  back a request the customer has already been told we received.
- **Bookings are read-only in origin** (`ResourceSchema::isReadOnlyOrigin`).
  Staff triage them; the admin has no "New booking" button and no delete, because
  a booking is a record of what a customer asked for.
- **Triage stamps `handled_by` and `handled_at`.** "Who confirmed this?" is the
  first question asked when a trip goes wrong.
- **The contact form has a honeypot** validated with `prohibited` — no custom
  logic, and bots fill it in.
- Both public POST routes are rate limited; they are open to the internet.

### Permissions

`PermissionSeeder` seeds `{resource}.{view,create,update,delete}` for all nine
resources (36 permissions) and three roles:

```text
admin    all 36
editor   the six catalogue resources, all abilities
viewer   view on everything, write on nothing
```

Only `.view` existed before, so every write route would have 403'd the moment
persistence became real.

## Bugs Found And Fixed

Recorded because each was silent — the system looked healthy while being wrong.

1. **Admin CRUD persisted nothing.** `store`/`update`/`destroy` flashed
   "Saved successfully" and redirected without touching the database.
2. **Reads swallowed every error.** `catch (Throwable) { return []; }` in the
   controller and `AdminDashboardService` turned a broken database into an empty
   list and invented dashboard counts. Removed; a failure now surfaces.
3. **Route parameters bound backwards.** `resource` is a route *default*, so
   Laravel appends it after the URI parameters: the bound order is
   `[id, resource]`. Controller signatures read `(string $resource, string $id)`,
   so `$resource` received `"10"` and **every edit and show page 404'd** while
   index and create kept working. Signatures now take `$id` first.
4. **The locale never left the `{locale}` routes.** `SetLocale` wrote
   `session('locale')` but nothing read it back, so `/login` and the whole
   `/admin` group always rendered in the default locale. Fixed with
   `ApplySessionLocale`; `config('app.supported_locales')` is now the single
   source of truth for the locale list.
5. **Toggles could not be switched off.** An unchecked checkbox posts nothing,
   so `is_active` could be turned on and never off. `x-forms.toggle` now emits a
   hidden `0` companion and reflects its current value (it never rendered
   `checked` at all).
6. **Relation selects never showed their selection.** `x-forms.select` compared
   `old($name) === $optionValue`, but PHP casts numeric array keys to int, so
   `"3" === 3` was always false.
7. **Passwords were echoed into the HTML.** `x-forms.input` rendered
   `value="{{ old(...) }}"` for every type, including `password`.
8. **The login page hardcoded the demo password** into the visible password
   field, training browsers to save it.
9. **No login throttling.** Now 5/minute per email+IP.
10. **`ActiveUser` middleware enforced nothing.** It checked an `is_active`
    column that did not exist, so the guard always passed. Column added; the
    check is real, and `Auth::attempt` now rejects disabled accounts outright.
11. **Password reset and email verification were view-only stubs** with no POST
    routes. The forms had no `method`, no `action`, no `@csrf`, and
    `type="button"`. Implemented; reset does not reveal whether an email is
    registered.
12. **`lang/*/passwords.php` did not exist**, so Arabic reset mail fell back to
    English and some keys rendered raw.
13. **Spatie Activity Log was installed but never migrated**, which is why the
    dashboard's activity feed was permanently empty.
14. **`__()` does not pluralize.** The dashboard rendered the literal
    `{1} Missing :count translation|[2,*] …` on screen. Now `trans_choice`, with
    Arabic's dual and 3-10 / 11+ forms.
15. **`@can('update', $resource)` silently denied everything.** These routes are
    keyed by a resource slug, not a bound model, so Gate could not resolve the
    string. Permissions are now checked by name.
16. **`/admin/settings` returned 500.** It is routed and in the sidebar but had
    no table; the old `catch (Throwable)` hid the crash. It is now a real form.
17. **The public language switcher ignored the `languages` table.** It was a
    hardcoded `['en', 'ar']` array, so the Russian row in the catalogue never
    appeared on the site and deactivating a language did nothing.
18. **`currentCurrency()` trusted the session blindly.** A session naming a
    currency that had since been deactivated or deleted was returned as-is. It
    now validates against the active list and falls back to the catalogue's
    default rather than a hardcoded `'USD'`.
19. **The footer's social icons pointed at `'#'`.** The links were hardcoded
    placeholders — and in fact the layout never rendered them at all. They are
    now real settings, and a blank one is omitted rather than rendered dead.
20. **The public Blade tests ran against a database with no tables.** They had
    no `RefreshDatabase` and passed only because every list they touched was a
    hardcoded array. They now seed the catalogue they actually depend on.
21. **The Save button silently did nothing on any form with 3+ languages.**
    Every locale's translated field was rendered `required`, but the non-default
    locales sit on hidden tabs — so the browser refused to submit and reported
    "please fill out this field" against an element nobody could see. No error,
    no request, no clue. This affected **tours, destinations and categories**,
    not just the new resources; it only surfaced once Russian was activated and
    a third tab appeared. Only the fallback locale is required now, matching
    `ResourceRequest` exactly.
22. **`PublicPageService::destinations()` ran a query per row** for its tour
    count, on the home page and the destinations index.
23. **Translations were inner-joined on the active locale**, so any row missing
    an Arabic translation disappeared from the Arabic site rather than falling
    back to English.
24. **The admin listing hardcoded `name` as the translated label column.** A
    blog post's is `title`; a testimonial's is `quote`. This broke the listing
    label, the search, and — worst — slug generation, which silently produced
    `item`, `item-2`, `item-3`… for every post.

25. **"Book Now" was a dead button** — `type="button"` wired to nothing, on
    the header, every tour card and the tour page. Clicking it did nothing.
26. **The Contact form discarded every message.** No `method`, no `action`, no
    `@csrf`, and `type="button"` on submit. Visitors typed an enquiry, pressed
    Send, and it went nowhere. There was no table to store one in either.
27. **A stock photo host reassigned a photo ID**, so a picture of a man in a gym
    rendered on the Luxor temple tour. The URL still returned HTTP 200, so
    nothing detected it. Worse, `tours` had **no `image_url` column at all** —
    the photo came from a hardcoded `match` on the tour code, so no admin could
    ever have fixed it. Column added; every hotlinked URL removed. A missing
    image is honest, a confidently wrong one is not.
28. **"Live chat" and "Cookie preferences" were dead buttons** floating over
    every page. Removed; WhatsApp is the one channel that actually reaches you.
29. **The admin listing rendered raw translation keys** (`admin.status.pending`)
    for any resource with its own status vocabulary. The lookup now falls back to
    the raw value rather than printing a key at a user.
30. **The bookings queue sorted oldest-first**, burying new requests under
    handled ones. Pending now sorts to the top, newest first.

## Deliberate Decisions

- **`MustVerifyEmail` is still not implemented.** `MAIL_MAILER=log`, so enabling
  it would lock out any new admin behind an email they would never receive. The
  `verified` middleware stays on the admin group and passes for existing users.
  **Enable both together, before production.**
- **`users` and `roles` have no `deleted_at`**, so deleting them is a force
  delete and is irreversible. Add soft deletes if that is not intended.
- **The Livewire tour wizard is no longer embedded in the tour form.** It stores
  nothing, and two competing editors on one screen is worse than one that works.
  The wizard is untouched and still routable.

## Verification

```bash
php artisan test                 # 111 passed
vendor/bin/pint --test app/      # passed
```

There is exactly one `catch (Throwable)` left on a read path, in
`EloquentSettingRepository::all()`. It is deliberate and it calls `report()`:
settings decorate the chrome, and a marketing page should not 500 over a footer
address. Writes still throw. Every other repository read surfaces its errors.

Driven in a real browser (Playwright, system Chrome):

```text
all nine admin resources        200, no console or network errors
create -> edit -> delete        flash + row appears, values reload, toggle
                                switches off and stays off, row soft-deleted
activity log                    3 rows: created / updated / deleted, attributed
tour edit form                  relations resolved, 3 locale tabs, translations
                                loaded
Arabic admin                    dir=rtl, sidebar and table mirrored
login                           desktop + mobile, EN + AR
```

The capacity chart's palette was validated rather than eyeballed: the first two
teal choices failed the contrast check (1.48:1 and 1.86:1 against white). The
shipped ramp is teal 500-900 on light `#ffffff` and teal 300-700 on dark
`#0f172a`, both passing monotonic-lightness, single-hue, and contrast checks.

## Next

```text
Decide on MustVerifyEmail + a real mailer together.
Port the tour wizard onto the repository, or retire it.
Add PostgreSQL-backed tests for departure capacity locking (batch-01 §Tests).
```
