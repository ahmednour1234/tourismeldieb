<?php

declare(strict_types=1);

namespace App\Admin;

use App\Models\BlogPost;
use App\Models\BookingRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Destination;
use App\Models\Language;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

/**
 * The single source of truth for what each admin resource *is*: which model
 * backs it, which fields it edits, and how each field validates.
 *
 * The admin used to render one generic name/code/status/active form for every
 * resource, which matched almost none of the real tables — a currency has no
 * `status`, a language has no `description`. Describing each resource once here
 * lets the form, the validation rules, and the repository stay in agreement,
 * rather than drifting apart across nine copies.
 *
 * Field types map to form controls: text, number, textarea, select, toggle,
 * relation (a belongsTo rendered as a select).
 */
final class ResourceSchema
{
    /**
     * Every resource the admin routes accept.
     *
     * @var list<string>
     */
    public const RESOURCES = [
        'languages', 'currencies', 'countries', 'destinations',
        'categories', 'tours', 'posts', 'testimonials', 'bookings',
        'users', 'roles', 'settings',
    ];

    /**
     * Resources whose rows carry translation rows in a sibling table.
     *
     * @var list<string>
     */
    public const TRANSLATABLE = ['destinations', 'categories', 'tours', 'posts', 'testimonials'];

    public static function exists(string $resource): bool
    {
        return in_array($resource, self::RESOURCES, true);
    }

    /**
     * Whether a resource is backed by a table at all.
     *
     * `settings` is routed and appears in the sidebar, but no settings table
     * exists yet — so it has no model, no fields, and nothing to list.
     */
    public static function hasModel(string $resource): bool
    {
        return self::exists($resource) && self::fields($resource) !== [];
    }

    /**
     * @return class-string<Model>
     */
    public static function model(string $resource): string
    {
        return match ($resource) {
            'languages' => Language::class,
            'currencies' => Currency::class,
            'countries' => Country::class,
            'destinations' => Destination::class,
            'categories' => TourCategory::class,
            'tours' => Tour::class,
            'posts' => BlogPost::class,
            'testimonials' => Testimonial::class,
            'bookings' => BookingRequest::class,
            'users' => User::class,
            'roles' => Role::class,
            default => throw new InvalidArgumentException("Resource [{$resource}] has no model."),
        };
    }

    public static function isTranslatable(string $resource): bool
    {
        return in_array($resource, self::TRANSLATABLE, true);
    }

    /**
     * The column rendered as a row's title in listings, for resources that
     * store their name directly rather than in a translation row.
     */
    public static function labelColumn(string $resource): string
    {
        return match ($resource) {
            'currencies' => 'code',
            'testimonials' => 'author_name',
            'bookings' => 'reference',
            default => 'name',
        };
    }

    /**
     * The translation column carrying a row's display title.
     *
     * Not every translatable resource names it `name`: a blog post has a
     * `title`, a testimonial only has its `quote`. Hardcoding `name` broke
     * listing labels, search, and slug generation for both.
     */
    public static function translationLabelColumn(string $resource): string
    {
        return match ($resource) {
            'posts' => 'title',
            'testimonials' => 'quote',
            default => 'name',
        };
    }

    /**
     * Whether a resource's translations carry a slug that must be generated
     * from its label when left blank. Testimonials have no public URL.
     */
    public static function hasSlug(string $resource): bool
    {
        return self::isTranslatable($resource) && $resource !== 'testimonials';
    }

    /**
     * Resources an admin may only read and triage, never author.
     *
     * A booking originates with a customer. Offering a "New booking" button
     * would invite staff to invent a request nobody made, and a delete button
     * would destroy the record of one.
     */
    public static function isReadOnlyOrigin(string $resource): bool
    {
        return $resource === 'bookings';
    }

    /**
     * Field definitions for the create/edit form.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fields(string $resource): array
    {
        return match ($resource) {
            'languages' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'max:10'], 'unique' => true, 'help' => 'admin.fields.code_help'],
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'native_name' => ['type' => 'text', 'label' => 'admin.fields.native_name', 'rules' => ['required', 'string', 'max:255']],
                'direction' => ['type' => 'select', 'label' => 'admin.fields.direction', 'options' => ['ltr' => 'LTR', 'rtl' => 'RTL'], 'rules' => ['required', 'in:ltr,rtl'], 'default' => 'ltr'],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            'currencies' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'size:3'], 'unique' => true, 'help' => 'admin.fields.currency_code_help'],
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'symbol' => ['type' => 'text', 'label' => 'admin.fields.symbol', 'rules' => ['required', 'string', 'max:10']],
                'decimal_places' => ['type' => 'number', 'label' => 'admin.fields.decimal_places', 'rules' => ['required', 'integer', 'min:0', 'max:4'], 'default' => 2],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_default' => ['type' => 'toggle', 'label' => 'admin.fields.is_default', 'rules' => ['boolean'], 'default' => false, 'help' => 'admin.fields.is_default_help'],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            'countries' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'size:2'], 'unique' => true, 'help' => 'admin.fields.country_code_help'],
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'phone_code' => ['type' => 'text', 'label' => 'admin.fields.phone_code', 'rules' => ['nullable', 'string', 'max:10']],
                'currency_id' => ['type' => 'relation', 'label' => 'admin.fields.currency', 'relation' => 'currencies', 'rules' => ['nullable', 'integer', 'exists:currencies,id']],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            'destinations' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'max:64'], 'unique' => true],
                'country_id' => ['type' => 'relation', 'label' => 'admin.fields.country', 'relation' => 'countries', 'rules' => ['nullable', 'integer', 'exists:countries,id']],
                'timezone' => ['type' => 'text', 'label' => 'admin.fields.timezone', 'rules' => ['nullable', 'string', 'max:64'], 'default' => 'Africa/Cairo'],
                'image_url' => ['type' => 'text', 'label' => 'admin.fields.image_url', 'rules' => ['nullable', 'string', 'max:2048']],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_featured' => ['type' => 'toggle', 'label' => 'admin.fields.is_featured', 'rules' => ['boolean'], 'default' => false],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            'categories' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'max:64'], 'unique' => true],
                'parent_id' => ['type' => 'relation', 'label' => 'admin.fields.parent', 'relation' => 'categories', 'rules' => ['nullable', 'integer', 'exists:tour_categories,id']],
                'image_url' => ['type' => 'text', 'label' => 'admin.fields.image_url', 'rules' => ['nullable', 'string', 'max:2048']],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_featured' => ['type' => 'toggle', 'label' => 'admin.fields.is_featured', 'rules' => ['boolean'], 'default' => false],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            'tours' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'max:64'], 'unique' => true],
                'destination_id' => ['type' => 'relation', 'label' => 'admin.fields.destination', 'relation' => 'destinations', 'rules' => ['required', 'integer', 'exists:destinations,id']],
                'tour_category_id' => ['type' => 'relation', 'label' => 'admin.fields.category', 'relation' => 'categories', 'rules' => ['nullable', 'integer', 'exists:tour_categories,id']],
                'status' => ['type' => 'select', 'label' => 'admin.fields.status', 'options' => ['draft' => 'admin.status.draft', 'published' => 'admin.status.published', 'archived' => 'admin.status.archived'], 'rules' => ['required', 'in:draft,published,archived'], 'default' => 'draft'],
                'duration_value' => ['type' => 'number', 'label' => 'admin.fields.duration_value', 'rules' => ['nullable', 'integer', 'min:1', 'max:32767']],
                'duration_unit' => ['type' => 'select', 'label' => 'admin.fields.duration_unit', 'options' => ['hours' => 'admin.units.hours', 'days' => 'admin.units.days'], 'rules' => ['nullable', 'in:hours,days'], 'default' => 'hours'],
                'tour_type' => ['type' => 'text', 'label' => 'admin.fields.tour_type', 'rules' => ['nullable', 'string', 'max:64']],
                'image_url' => ['type' => 'text', 'label' => 'admin.fields.image_url', 'rules' => ['nullable', 'string', 'max:2048']],
                'minimum_age' => ['type' => 'number', 'label' => 'admin.fields.minimum_age', 'rules' => ['nullable', 'integer', 'min:0', 'max:120']],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_featured' => ['type' => 'toggle', 'label' => 'admin.fields.is_featured', 'rules' => ['boolean'], 'default' => false],
                'is_best_seller' => ['type' => 'toggle', 'label' => 'admin.fields.is_best_seller', 'rules' => ['boolean'], 'default' => false],
                'is_last_minute' => ['type' => 'toggle', 'label' => 'admin.fields.is_last_minute', 'rules' => ['boolean'], 'default' => false],
            ],
            'posts' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'max:64'], 'unique' => true],
                'status' => ['type' => 'select', 'label' => 'admin.fields.status', 'options' => ['draft' => 'admin.status.draft', 'published' => 'admin.status.published', 'archived' => 'admin.status.archived'], 'rules' => ['required', 'in:draft,published,archived'], 'default' => 'draft'],
                'published_at' => ['type' => 'datetime', 'label' => 'admin.fields.published_at', 'rules' => ['nullable', 'date'], 'help' => 'admin.fields.published_at_help'],
                'image_url' => ['type' => 'text', 'label' => 'admin.fields.image_url', 'rules' => ['nullable', 'string', 'max:2048']],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_featured' => ['type' => 'toggle', 'label' => 'admin.fields.is_featured', 'rules' => ['boolean'], 'default' => false],
            ],
            'testimonials' => [
                'code' => ['type' => 'text', 'label' => 'admin.fields.code', 'rules' => ['required', 'string', 'max:64'], 'unique' => true],
                'author_name' => ['type' => 'text', 'label' => 'admin.fields.author_name', 'rules' => ['required', 'string', 'max:255']],
                'author_country' => ['type' => 'text', 'label' => 'admin.fields.author_country', 'rules' => ['nullable', 'string', 'size:2'], 'help' => 'admin.fields.country_code_help'],
                'tour_id' => ['type' => 'relation', 'label' => 'admin.fields.tour', 'relation' => 'tours', 'rules' => ['nullable', 'integer', 'exists:tours,id'], 'help' => 'admin.fields.tour_help'],
                'rating' => ['type' => 'number', 'label' => 'admin.fields.rating', 'rules' => ['nullable', 'integer', 'min:1', 'max:5'], 'help' => 'admin.fields.rating_help'],
                'reviewed_on' => ['type' => 'date', 'label' => 'admin.fields.reviewed_on', 'rules' => ['nullable', 'date']],
                'avatar_url' => ['type' => 'text', 'label' => 'admin.fields.avatar_url', 'rules' => ['nullable', 'string', 'max:2048']],
                'sort_order' => ['type' => 'number', 'label' => 'admin.fields.sort_order', 'rules' => ['nullable', 'integer', 'min:0', 'max:32767'], 'default' => 0],
                'is_featured' => ['type' => 'toggle', 'label' => 'admin.fields.is_featured', 'rules' => ['boolean'], 'default' => false],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            // A booking is a record of what a customer asked for. Staff triage
            // it — they do not rewrite the customer's request, so the request
            // itself is not editable here. Only the operator's own columns are.
            'bookings' => [
                'status' => ['type' => 'select', 'label' => 'admin.fields.status', 'options' => ['pending' => 'admin.booking_status.pending', 'confirmed' => 'admin.booking_status.confirmed', 'cancelled' => 'admin.booking_status.cancelled', 'completed' => 'admin.booking_status.completed'], 'rules' => ['required', 'in:pending,confirmed,cancelled,completed'], 'default' => 'pending'],
                'admin_notes' => ['type' => 'textarea', 'label' => 'admin.fields.admin_notes', 'rules' => ['nullable', 'string', 'max:2000'], 'help' => 'admin.fields.admin_notes_help'],
            ],
            'users' => [
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'email' => ['type' => 'text', 'label' => 'admin.fields.email', 'rules' => ['required', 'email', 'max:255'], 'unique' => true],
                'password' => ['type' => 'password', 'label' => 'admin.fields.password', 'rules' => ['nullable', 'string', 'min:8'], 'help' => 'admin.fields.password_help'],
                'is_active' => ['type' => 'toggle', 'label' => 'admin.fields.is_active', 'rules' => ['boolean'], 'default' => true],
            ],
            'roles' => [
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
            ],
            default => [],
        };
    }

    /**
     * The translated fields rendered under the per-locale tabs.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function translationFields(string $resource): array
    {
        return match ($resource) {
            'destinations' => [
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'slug' => ['type' => 'text', 'label' => 'admin.fields.slug', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'admin.fields.slug_help'],
                'short_description' => ['type' => 'textarea', 'label' => 'admin.fields.short_description', 'rules' => ['nullable', 'string', 'max:500']],
                'description' => ['type' => 'textarea', 'label' => 'admin.fields.description', 'rules' => ['nullable', 'string']],
            ],
            'categories' => [
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'slug' => ['type' => 'text', 'label' => 'admin.fields.slug', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'admin.fields.slug_help'],
                'description' => ['type' => 'textarea', 'label' => 'admin.fields.description', 'rules' => ['nullable', 'string']],
            ],
            'tours' => [
                'name' => ['type' => 'text', 'label' => 'admin.fields.name', 'rules' => ['required', 'string', 'max:255']],
                'slug' => ['type' => 'text', 'label' => 'admin.fields.slug', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'admin.fields.slug_help'],
                'short_description' => ['type' => 'textarea', 'label' => 'admin.fields.short_description', 'rules' => ['nullable', 'string', 'max:500']],
                'description' => ['type' => 'textarea', 'label' => 'admin.fields.description', 'rules' => ['nullable', 'string']],
                'seo_title' => ['type' => 'text', 'label' => 'admin.fields.seo_title', 'rules' => ['nullable', 'string', 'max:255']],
                'seo_description' => ['type' => 'textarea', 'label' => 'admin.fields.seo_description', 'rules' => ['nullable', 'string', 'max:500']],
            ],
            'posts' => [
                'title' => ['type' => 'text', 'label' => 'admin.fields.title', 'rules' => ['required', 'string', 'max:255']],
                'slug' => ['type' => 'text', 'label' => 'admin.fields.slug', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'admin.fields.slug_help'],
                'excerpt' => ['type' => 'textarea', 'label' => 'admin.fields.excerpt', 'rules' => ['nullable', 'string', 'max:500']],
                'body' => ['type' => 'textarea', 'label' => 'admin.fields.body', 'rules' => ['nullable', 'string']],
                'seo_title' => ['type' => 'text', 'label' => 'admin.fields.seo_title', 'rules' => ['nullable', 'string', 'max:255']],
                'seo_description' => ['type' => 'textarea', 'label' => 'admin.fields.seo_description', 'rules' => ['nullable', 'string', 'max:500']],
            ],
            'testimonials' => [
                'quote' => ['type' => 'textarea', 'label' => 'admin.fields.quote', 'rules' => ['required', 'string', 'max:1000']],
            ],
            default => [],
        };
    }

    /**
     * The table a resource's rows live in — needed for unique rules.
     */
    public static function table(string $resource): string
    {
        /** @var Model $model */
        $model = new (self::model($resource))();

        return $model->getTable();
    }
}
