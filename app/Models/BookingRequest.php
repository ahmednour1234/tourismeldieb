<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BookingRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'tour_id',
        'tour_option_id',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'preferred_date',
        'adults',
        'children',
        'infants',
        'notes',
        'locale',
        'status',
        'admin_notes',
        'handled_at',
        'handled_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'adults' => 'integer',
            'children' => 'integer',
            'infants' => 'integer',
            'handled_at' => 'datetime',
        ];
    }

    /**
     * A short, unambiguous reference the customer can quote.
     *
     * Excludes characters that are easily confused when read aloud or
     * hand-written (0/O, 1/I) — support staff have to type these back.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'HG-'.Str::upper(Str::random(6, 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'));
        } while (static::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @return BelongsTo<Tour, $this>
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getTotalGuestsAttribute(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * @param  Builder<BookingRequest>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }
}
