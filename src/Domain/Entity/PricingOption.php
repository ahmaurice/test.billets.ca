<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;
use InvalidArgumentException;

final readonly class PricingOption
{
    private function __construct(
        private string $name,
        private Money $price,
        private int $durationInMonths
    ) {
        if ($durationInMonths <= 0) {
            throw new InvalidArgumentException('Duration must be positive');
        }
    }

    public static function create(string $name, Money $price, int $durationInMonths): self
    {
        return new self($name, $price, $durationInMonths);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getDurationInMonths(): int
    {
        return $this->durationInMonths;
    }
}
