<?php

declare(strict_types=1);

namespace App\Application\UseCase\CancelSubscription;

final readonly class CancelSubscriptionCommand
{
    public function __construct(
        public string $subscriptionId
    ) {
    }
}
