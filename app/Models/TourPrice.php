<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One price line: what a given guest type pays for a given option, in a given
 * currency, optionally only between two dates.
 *
 * `amount_minor` is stored in the currency's smallest unit (cents), so nothing
 * in the pricing path ever rounds a float. The admin form takes and shows a
 * major-unit amount and converts on the way in and out — see
 * ResourceSchema::fields('prices') and the money field type.
 */
class TourPrice extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tour_option_id',
        'guest_type',
        'currency_id',
        'amount_minor',
        'minimum_quantity',
        'maximum_quantity',
        'valid_from',
        'valid_to',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'minimum_quantity' => 'integer',
            'maximum_quantity' => 'integer',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<TourOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(TourOption::class, 'tour_option_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
