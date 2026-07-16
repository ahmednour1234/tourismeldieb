<?php

declare(strict_types=1);

namespace App\Shared\Money;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public int $minorAmount,
        public string $currency,
    ) {
        if ($minorAmount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Currency must be a three-letter ISO code.');
        }
    }

    public static function zero(string $currency): self
    {
        return new self(0, strtoupper($currency));
    }

    public function add(self $money): self
    {
        $this->assertSameCurrency($money);

        return new self($this->minorAmount + $money->minorAmount, $this->currency);
    }

    public function subtract(self $money): self
    {
        $this->assertSameCurrency($money);

        return new self(max(0, $this->minorAmount - $money->minorAmount), $this->currency);
    }

    private function assertSameCurrency(self $money): void
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidArgumentException('Money currency mismatch.');
        }
    }
}
