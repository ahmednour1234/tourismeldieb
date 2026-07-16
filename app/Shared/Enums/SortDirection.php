<?php

declare(strict_types=1);

namespace App\Shared\Enums;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';

    public static function fromNullable(?string $direction): self
    {
        return match (strtolower((string) $direction)) {
            self::Ascending->value => self::Ascending,
            default => self::Descending,
        };
    }
}
