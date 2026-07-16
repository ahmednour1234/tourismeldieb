<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * An immutable carrier for one admin resource write.
 *
 * The validated payload arrives as a single flat array with a nested
 * `translations` key. Splitting it once here means every layer downstream —
 * action, service, repository — receives the same shape, rather than each one
 * re-deriving "which of these keys are columns and which are translations?"
 */
final readonly class ResourceData
{
    /**
     * @param  array<string, mixed>  $attributes  Column values for the resource's own table.
     * @param  array<string, array<string, mixed>>  $translations  Keyed by locale.
     */
    public function __construct(
        public string $resource,
        public array $attributes = [],
        public array $translations = [],
    ) {}

    /**
     * Split a validated payload into columns and per-locale translation rows.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromRequest(string $resource, array $validated): self
    {
        /** @var array<string, array<string, mixed>> $translations */
        $translations = $validated['translations'] ?? [];

        unset($validated['translations']);

        return new self($resource, $validated, $translations);
    }
}
