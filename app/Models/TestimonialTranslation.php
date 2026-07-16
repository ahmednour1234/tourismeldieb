<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestimonialTranslation extends Model
{
    protected $fillable = [
        'testimonial_id',
        'locale',
        'quote',
    ];

    /**
     * @return BelongsTo<Testimonial, $this>
     */
    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }
}
