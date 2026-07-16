<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Admin\ResourceSchema;
use App\Shared\Contracts\ResourceRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The only class that touches the database for admin resources.
 *
 * Every resource shares one code path, driven by ResourceSchema, because nine
 * near-identical repositories drift: a fix applied to tours silently misses
 * destinations. Where a resource genuinely differs — a hashed password, a
 * single default currency — that difference is expressed as one narrow branch
 * rather than a whole duplicated class.
 */
final class EloquentResourceRepository implements ResourceRepositoryContract
{
    /**
     * Memoised `table.column` existence answers.
     *
     * Schema::hasColumn issues a real introspection query. Called per row while
     * stamping created_by, it would turn one insert into N round-trips.
     *
     * @var array<string, bool>
     */
    private array $columnCache = [];

    /**
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(string $resource, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query($resource)->with($this->eagerLoads($resource));

        if ($search !== null && $search !== '') {
            $this->applySearch($query, $resource, $search);
        }

        $this->applyOrder($query, $resource);

        /** @var LengthAwarePaginator<int, Model> $paginator */
        $paginator = $query->paginate($perPage)->withQueryString();

        return $paginator;
    }

    public function find(string $resource, int|string $id): ?Model
    {
        return $this->query($resource)
            ->with($this->eagerLoads($resource))
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function create(string $resource, array $attributes, array $translations = []): Model
    {
        return DB::transaction(function () use ($resource, $attributes, $translations): Model {
            $attributes = $this->prepareAttributes($resource, $attributes, isCreate: true);

            /** @var Model $model */
            $model = $this->newModel($resource);
            $model->fill($attributes);
            $model->save();

            $this->syncTranslations($resource, $model, $translations);
            $this->enforceSingleDefaultCurrency($resource, $model, $attributes);

            return $model;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function update(string $resource, Model $model, array $attributes, array $translations = []): Model
    {
        return DB::transaction(function () use ($resource, $model, $attributes, $translations): Model {
            $attributes = $this->prepareAttributes($resource, $attributes, isCreate: false);

            $model->fill($attributes);
            $model->save();

            $this->syncTranslations($resource, $model, $translations);
            $this->enforceSingleDefaultCurrency($resource, $model, $attributes);

            return $model;
        });
    }

    public function delete(string $resource, Model $model): void
    {
        DB::transaction(function () use ($model): void {
            if ($this->usesSoftDeletes($model)) {
                $model->delete();

                return;
            }

            $model->forceDelete();
        });
    }

    /**
     * Options for a relation select, as id => label in the active locale.
     *
     * @return array<int|string, string>
     */
    public function options(string $resource): array
    {
        $query = $this->query($resource);

        if (ResourceSchema::isTranslatable($resource)) {
            $this->applyOrder($query, $resource);

            return $query->with('translation')
                ->get()
                ->mapWithKeys(fn (Model $model): array => [
                    $model->getKey() => $this->translatedLabel($model, $resource),
                ])
                ->all();
        }

        $label = ResourceSchema::labelColumn($resource);

        return $query->orderBy($label)
            ->pluck($label, $this->newModel($resource)->getKeyName())
            ->map(fn (mixed $value): string => (string) $value)
            ->all();
    }

    /**
     * Relations to eager-load so a listing never issues a query per row.
     *
     * @return list<string>
     */
    private function eagerLoads(string $resource): array
    {
        $relations = [];

        if (ResourceSchema::isTranslatable($resource)) {
            $relations[] = 'translation';
        }

        return match ($resource) {
            'countries' => ['currency'],
            'tours' => [...$relations, 'destination.translation', 'category.translation'],
            // The edit screen names the tour and the staff member who handled
            // it; without these the listing would query per row.
            'bookings' => ['tour.translation', 'handler'],
            default => $relations,
        };
    }

    /**
     * Search the label column, or the translation name for translatable rows.
     *
     * @param  Builder<Model>  $query
     */
    private function applySearch(Builder $query, string $resource, string $search): void
    {
        $term = '%'.$search.'%';

        // Staff search a booking by whatever the customer quoted at them: the
        // reference from their email, their name, or their address.
        if ($resource === 'bookings') {
            $query->where(function (Builder $outer) use ($term): void {
                $outer->where('reference', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_email', 'like', $term);
            });

            return;
        }

        if (ResourceSchema::isTranslatable($resource)) {
            $column = ResourceSchema::translationLabelColumn($resource);

            $query->where(function (Builder $outer) use ($resource, $column, $term): void {
                $outer->whereHas('translations', fn (Builder $sub): Builder => $sub->where($column, 'like', $term));

                // A testimonial's translation holds only the quote, so searching
                // by the author's name — the obvious thing to type — would
                // otherwise find nothing.
                if ($resource === 'testimonials') {
                    $outer->orWhere('author_name', 'like', $term);
                }
            });

            return;
        }

        $query->where(ResourceSchema::labelColumn($resource), 'like', $term);
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyOrder(Builder $query, string $resource): void
    {
        $model = $this->newModel($resource);

        // Newest first, and anything still awaiting a reply above that: a
        // booking queue sorted oldest-first buries the requests that need
        // answering under the ones already dealt with.
        if ($resource === 'bookings') {
            $query->orderByRaw("case when status = 'pending' then 0 else 1 end")
                ->orderByDesc('created_at');

            return;
        }

        if ($this->hasColumn($model->getTable(), 'sort_order')) {
            $query->orderBy('sort_order');
            $query->orderBy($model->getKeyName());

            return;
        }

        if (ResourceSchema::isTranslatable($resource)) {
            $query->orderBy($model->getKeyName());

            return;
        }

        $query->orderBy(ResourceSchema::labelColumn($resource));
    }

    /**
     * Stamp audit columns and normalise resource-specific values.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareAttributes(string $resource, array $attributes, bool $isCreate): array
    {
        $attributes = $this->prepareUserPassword($resource, $attributes, $isCreate);

        $table = ResourceSchema::table($resource);
        $userId = auth()->id();

        if ($userId !== null) {
            if ($isCreate && $this->hasColumn($table, 'created_by')) {
                $attributes['created_by'] = $userId;
            }

            if ($this->hasColumn($table, 'updated_by')) {
                $attributes['updated_by'] = $userId;
            }

            // Triaging a booking records who did it and when: "who confirmed
            // this?" is the first question asked when a trip goes wrong.
            if ($resource === 'bookings' && ! $isCreate) {
                $attributes['handled_by'] = $userId;
                $attributes['handled_at'] = now();
            }
        }

        return $attributes;
    }

    /**
     * Hash a supplied password; never blank an existing one out.
     *
     * On update the password field is optional. An empty string means "leave it
     * alone" — writing it through would lock the user out of their own account.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareUserPassword(string $resource, array $attributes, bool $isCreate): array
    {
        if ($resource !== 'users' || ! array_key_exists('password', $attributes)) {
            return $attributes;
        }

        $password = $attributes['password'];

        if (! is_string($password) || trim($password) === '') {
            unset($attributes['password']);

            return $attributes;
        }

        $attributes['password'] = Hash::make($password);

        return $attributes;
    }

    /**
     * Upsert one translation row per supplied locale.
     *
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function syncTranslations(string $resource, Model $model, array $translations): void
    {
        if (! ResourceSchema::isTranslatable($resource) || $translations === []) {
            return;
        }

        /** @var HasMany<Model, Model> $relation */
        $relation = $model->translations();
        $foreignKey = $relation->getForeignKeyName();

        foreach ($translations as $locale => $payload) {
            if ($this->isEmptyPayload($payload)) {
                continue;
            }

            // Not every translatable resource has a public URL: a testimonial's
            // translation is just the quote, with no slug column to write to.
            if (ResourceSchema::hasSlug($resource)) {
                $payload['slug'] = $this->resolveSlug(
                    $relation->getRelated(),
                    $payload,
                    (string) $locale,
                    $foreignKey,
                    $model->getKey(),
                    $resource,
                );
            }

            $relation->updateOrCreate(
                [$foreignKey => $model->getKey(), 'locale' => $locale],
                $payload,
            );
        }
    }

    /**
     * A locale tab the editor never filled in should not create a row.
     *
     * @param  array<string, mixed>  $payload
     */
    private function isEmptyPayload(array $payload): bool
    {
        foreach ($payload as $value) {
            if (is_array($value) ? $value !== [] : trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Derive a slug from the name when blank, then make it unique per locale.
     *
     * The (locale, slug) unique index is global across the translation table, so
     * uniqueness is checked against every other row in that locale — excluding
     * this parent's own row, which would otherwise collide with itself on edit.
     *
     * @param  array<string, mixed>  $payload
     */
    private function resolveSlug(Model $translation, array $payload, string $locale, string $foreignKey, mixed $parentKey, string $resource): string
    {
        $slug = trim((string) ($payload['slug'] ?? ''));

        if ($slug === '') {
            // Derived from the resource's own label column: a blog post's is
            // `title`, not `name`, and reading the wrong key silently produced
            // "item", "item-2", "item-3"… for every post.
            $slug = (string) ($payload[ResourceSchema::translationLabelColumn($resource)] ?? '');
        }

        $base = Str::slug($slug);

        if ($base === '') {
            $base = 'item';
        }

        $candidate = $base;
        $suffix = 1;

        while ($this->slugTaken($translation, $locale, $candidate, $foreignKey, $parentKey)) {
            $suffix++;
            $candidate = $base.'-'.$suffix;
        }

        return $candidate;
    }

    private function slugTaken(Model $translation, string $locale, string $slug, string $foreignKey, mixed $parentKey): bool
    {
        return $translation->newQuery()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($parentKey !== null, fn (Builder $query): Builder => $query->where($foreignKey, '!=', $parentKey))
            ->exists();
    }

    /**
     * Exactly one currency may carry is_default.
     *
     * Runs inside the caller's transaction, so the brief window where two rows
     * are both default is never observable to another connection.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function enforceSingleDefaultCurrency(string $resource, Model $model, array $attributes): void
    {
        if ($resource !== 'currencies' || ! array_key_exists('is_default', $attributes)) {
            return;
        }

        if (! (bool) $attributes['is_default']) {
            return;
        }

        $model->newQuery()
            ->where($model->getKeyName(), '!=', $model->getKey())
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    private function translatedLabel(Model $model, string $resource): string
    {
        /** @var Model|null $translation */
        $translation = $model->getRelationValue('translation');

        $name = $translation?->getAttribute(ResourceSchema::translationLabelColumn($resource));

        return is_string($name) && $name !== '' ? $name : '#'.$model->getKey();
    }

    /**
     * @return Builder<Model>
     */
    private function query(string $resource): Builder
    {
        return $this->newModel($resource)->newQuery();
    }

    private function newModel(string $resource): Model
    {
        $class = ResourceSchema::model($resource);

        return new $class;
    }

    private function usesSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return $this->columnCache[$table.'.'.$column] ??= Schema::hasColumn($table, $column);
    }
}
