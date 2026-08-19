<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourOptionTranslation extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'tour_id',
        'tour_option_id',
        'locale',
        'name',
        'slug',
        'short_description',
        'description',
    ];

    /**
     * Mirror the parent option's tour onto the translation row.
     *
     * `tour_option_translations.tour_id` is NOT NULL and carries a unique index
     * on (tour_id, locale, slug), but the shared admin repository only ever
     * writes a translation's own foreign key — it has no way to know this table
     * denormalises a grandparent. Filling it here keeps the generic write path
     * working instead of special-casing one resource inside the repository.
     */
    protected static function booted(): void
    {
        static::saving(function (self $translation): void {
            if ($translation->tour_id !== null) {
                return;
            }

            $translation->tour_id = TourOption::withTrashed()
                ->whereKey($translation->tour_option_id)
                ->value('tour_id');
        });
    }

    /**
     * @return BelongsTo<TourOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(TourOption::class, 'tour_option_id');
    }
}
