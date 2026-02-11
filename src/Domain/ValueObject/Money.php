<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(
        private float $amount,
        private string $currency
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
        if (empty($currency)) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }
    }

    public static function create(float $amount, string $currency): self
    {
        return new self($amount, strtoupper($currency));
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    public function toString(): string
    {
        return sprintf('%.2f %s', $this->amount, $this->currency);
    }
}
