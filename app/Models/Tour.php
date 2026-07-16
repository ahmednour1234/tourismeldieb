<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'destination_id',
        'tour_category_id',
        'code',
        'image_url',
        'status',
        'duration_value',
        'duration_unit',
        'tour_type',
        'is_featured',
        'is_best_seller',
        'is_last_minute',
        'minimum_age',
        'sort_order',
        'published_at',
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
            'duration_value' => 'integer',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_last_minute' => 'boolean',
            'minimum_age' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The destination this tour runs at.
     *
     * @return BelongsTo<Destination, $this>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * The category this tour belongs to.
     *
     * @return BelongsTo<TourCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }

    /**
     * All translations for this tour.
     *
     * @return HasMany<TourTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TourTranslation::class);
    }

    /**
     * The user who created this tour.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this tour.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope the query to only published tours.
     *
     * @param  Builder<Tour>  $query
     * @return Builder<Tour>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
