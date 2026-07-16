<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourCategory extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'code',
        'image_url',
        'is_featured',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The parent category.
     *
     * @return BelongsTo<TourCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'parent_id');
    }

    /**
     * The direct child categories.
     *
     * @return HasMany<TourCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(TourCategory::class, 'parent_id');
    }

    /**
     * All translations for this category.
     *
     * @return HasMany<TourCategoryTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TourCategoryTranslation::class);
    }

    /**
     * The tours in this category.
     *
     * @return HasMany<Tour, $this>
     */
    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    /**
     * The user who created this category.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this category.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope the query to only active categories.
     *
     * @param  Builder<TourCategory>  $query
     * @return Builder<TourCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
