<?php

declare(strict_types=1);

namespace App\Application\DTO;

use DateTimeImmutable;

final readonly class SubscriptionDTO
{
    public function __construct(
        public string $id,
        public string $productName,
        public string $pricingOption,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public bool $isCancelled
    ) {
    }
}
