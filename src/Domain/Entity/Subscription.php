<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Period;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use InvalidArgumentException;

final class Subscription
{
    private ?DateTimeImmutable $cancelledAt = null;
    private DateTimeImmutable $createdAt;

    private function __construct(
        private SubscriptionId $id,
        private UserId $userId,
        private ProductId $productId,
        private string $pricingOptionName,
        private Period $period
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        SubscriptionId $id,
        UserId $userId,
        ProductId $productId,
        string $pricingOptionName,
        Period $period
    ): self {
        return new self($id, $userId, $productId, $pricingOptionName, $period);
    }

    public function getId(): SubscriptionId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getPricingOptionName(): string
    {
        return $this->pricingOptionName;
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCancelledAt(): ?DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function cancel(DateTimeImmutable $cancelledAt = null): void
    {
        if ($this->isCancelled()) {
            throw new InvalidArgumentException('Subscription is already cancelled');
        }

        $this->cancelledAt = $cancelledAt ?? new DateTimeImmutable();
    }

    public function isCancelled(): bool
    {
        return $this->cancelledAt !== null;
    }

    public function isActive(DateTimeImmutable $now = null): bool
    {
        $now = $now ?? new DateTimeImmutable();
        return $this->period->isActive($now);
    }

    public function isActiveAndNotCancelled(DateTimeImmutable $now = null): bool
    {
        return $this->isActive($now) && !$this->isCancelled();
    }
}
