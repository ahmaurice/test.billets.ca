<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Period
{
    private function __construct(
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate
    ) {
        if ($endDate <= $startDate) {
            throw new InvalidArgumentException('End date must be after start date');
        }
    }

    public static function create(DateTimeImmutable $startDate, DateTimeImmutable $endDate): self
    {
        return new self($startDate, $endDate);
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isActive(DateTimeImmutable $now = null): bool
    {
        $now = $now ?? new DateTimeImmutable();
        return $now >= $this->startDate && $now <= $this->endDate;
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }
}
