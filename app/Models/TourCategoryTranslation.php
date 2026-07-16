<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCategoryTranslation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tour_category_id',
        'locale',
        'name',
        'slug',
        'description',
    ];

    /**
     * The tour category this translation belongs to.
     *
     * @return BelongsTo<TourCategory, $this>
     */
    public function tourCategory(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class);
    }
}
