<?php

declare(strict_types=1);

namespace App\Application\UseCase\CancelSubscription;

use App\Domain\Exception\SubscriptionNotFoundException;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\ValueObject\SubscriptionId;

final readonly class CancelSubscriptionHandler
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    public function handle(CancelSubscriptionCommand $command): void
    {
        $subscriptionId = SubscriptionId::fromString($command->subscriptionId);

        $subscription = $this->subscriptionRepository->findById($subscriptionId);
        if ($subscription === null) {
            throw SubscriptionNotFoundException::withId($command->subscriptionId);
        }

        $subscription->cancel();

        $this->subscriptionRepository->save($subscription);
    }
}
