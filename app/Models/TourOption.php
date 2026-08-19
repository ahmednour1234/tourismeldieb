<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A bookable variant of a tour — "Shared Boat Trip", "Private Yacht".
 *
 * Prices hang off options rather than off tours, because the same trip sells at
 * different rates depending on which package the customer picks.
 */
class TourOption extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tour_id',
        'code',
        'capacity',
        'minimum_guests',
        'maximum_guests',
        'maximum_booking_quantity',
        'duration_value',
        'duration_unit',
        'is_private',
        'requires_manual_confirmation',
        'is_default',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'minimum_guests' => 'integer',
            'maximum_guests' => 'integer',
            'maximum_booking_quantity' => 'integer',
            'duration_value' => 'integer',
            'is_private' => 'boolean',
            'requires_manual_confirmation' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Tour, $this>
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * @return HasMany<TourOptionTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TourOptionTranslation::class);
    }

    /**
     * @return HasMany<TourPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(TourPrice::class);
    }
}
