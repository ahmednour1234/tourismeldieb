<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A business rule refused an otherwise well-formed request.
 *
 * Distinct from a validation error: the payload was fine, but performing the
 * action would leave the catalog in a state the domain does not permit —
 * deleting the last active language, or the default currency. Validation cannot
 * catch these, because they depend on the state of other rows.
 */
final class DomainActionException extends RuntimeException
{
    public static function lastActiveLanguage(): self
    {
        return new self(__('admin.errors.last_active_language'));
    }

    public static function defaultCurrency(): self
    {
        return new self(__('admin.errors.default_currency'));
    }
}
