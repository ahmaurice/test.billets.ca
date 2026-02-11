<?php

declare(strict_types=1);

namespace App\Application\UseCase\SubscribeUserToProduct;

use App\Domain\Entity\Subscription;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Period;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final readonly class SubscribeUserToProductHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ProductRepositoryInterface $productRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    public function handle(SubscribeUserToProductCommand $command): SubscriptionId
    {
        $userId = UserId::fromString($command->userId);
        $productId = ProductId::fromString($command->productId);

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            throw UserNotFoundException::withId($command->userId);
        }

        $product = $this->productRepository->findById($productId);
        if ($product === null) {
            throw ProductNotFoundException::withId($command->productId);
        }

        $pricingOption = $product->getPricingOptionByName($command->pricingOptionName);

        $startDate = new DateTimeImmutable();
        $endDate = $startDate->modify("+{$pricingOption->getDurationInMonths()} months");
        $period = Period::create($startDate, $endDate);

        $subscriptionId = SubscriptionId::generate();
        $subscription = Subscription::create(
            $subscriptionId,
            $userId,
            $productId,
            $command->pricingOptionName,
            $period
        );

        $this->subscriptionRepository->save($subscription);

        return $subscriptionId;
    }
}
