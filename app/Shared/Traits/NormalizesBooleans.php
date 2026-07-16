<?php

declare(strict_types=1);

namespace App\Shared\Traits;

trait NormalizesBooleans
{
    protected static function booleanValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected static function nullableBooleanValue(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::booleanValue($value);
    }
}
