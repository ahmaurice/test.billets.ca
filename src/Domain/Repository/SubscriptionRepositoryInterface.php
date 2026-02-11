<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Subscription;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $subscription): void;

    public function findById(SubscriptionId $id): ?Subscription;

    /**
     * @return array<Subscription>
     */
    public function findByUserId(UserId $userId): array;

    /**
     * @return array<Subscription>
     */
    public function findAll(): array;
}
