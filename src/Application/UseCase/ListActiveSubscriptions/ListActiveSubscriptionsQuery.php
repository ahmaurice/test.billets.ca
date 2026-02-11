<?php

declare(strict_types=1);

namespace App\Application\UseCase\ListActiveSubscriptions;

final readonly class ListActiveSubscriptionsQuery
{
    public function __construct(
        public string $userId
    ) {
    }
}
