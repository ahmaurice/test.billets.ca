<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;

final class InMemorySubscriptionRepository implements SubscriptionRepositoryInterface
{
    /** @var array<string, Subscription> */
    private array $subscriptions = [];

    public function save(Subscription $subscription): void
    {
        $this->subscriptions[$subscription->getId()->toString()] = $subscription;
    }

    public function findById(SubscriptionId $id): ?Subscription
    {
        return $this->subscriptions[$id->toString()] ?? null;
    }

    public function findByUserId(UserId $userId): array
    {
        return array_values(
            array_filter(
                $this->subscriptions,
                fn(Subscription $sub) => $sub->getUserId()->equals($userId)
            )
        );
    }

    public function findAll(): array
    {
        return array_values($this->subscriptions);
    }

    public function clear(): void
    {
        $this->subscriptions = [];
    }
}
