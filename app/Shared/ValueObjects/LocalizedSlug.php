<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class LocalizedSlug
{
    public function __construct(
        public string $locale,
        public string $slug,
    ) {
        if (! preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $locale)) {
            throw new InvalidArgumentException('Locale must use a valid language or language-region code.');
        }

        if ($slug === '') {
            throw new InvalidArgumentException('Slug cannot be empty.');
        }
    }
}
