<?php

declare(strict_types=1);

namespace App\Shared\DTOs;

interface DataTransferObject
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
