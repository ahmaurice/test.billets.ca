<?php

declare(strict_types=1);

namespace App\Application\UseCase\ListActiveSubscriptions;

use App\Application\DTO\SubscriptionDTO;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\UserId;

final readonly class ListActiveSubscriptionsHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /** @return array<SubscriptionDTO> */
    public function handle(ListActiveSubscriptionsQuery $query): array
    {
        $userId = UserId::fromString($query->userId);

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            throw UserNotFoundException::withId($query->userId);
        }

        $subscriptions = $this->subscriptionRepository->findByUserId($userId);

        $result = [];
        foreach ($subscriptions as $subscription) {
            if (!$subscription->isActive()) {
                continue;
            }

            $product = $this->productRepository->findById($subscription->getProductId());
            if ($product === null) {
                continue;
            }

            $result[] = new SubscriptionDTO(
                $subscription->getId()->toString(),
                $product->getName(),
                $subscription->getPricingOptionName(),
                $subscription->getPeriod()->getStartDate(),
                $subscription->getPeriod()->getEndDate(),
                $subscription->isCancelled()
            );
        }

        return $result;
    }
}
