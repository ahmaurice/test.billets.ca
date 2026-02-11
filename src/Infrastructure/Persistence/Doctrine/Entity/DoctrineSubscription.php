<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Entity\Subscription;
use App\Domain\ValueObject\Period;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'subscriptions')]
class DoctrineSubscription
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $userId;

    #[ORM\Column(type: 'string', length: 36)]
    private string $productId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $pricingOptionName;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $endDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(Subscription $subscription): self
    {
        $doctrineSubscription = new self();
        $doctrineSubscription->id = $subscription->getId()->toString();
        $doctrineSubscription->userId = $subscription->getUserId()->toString();
        $doctrineSubscription->productId = $subscription->getProductId()->toString();
        $doctrineSubscription->pricingOptionName = $subscription->getPricingOptionName();
        $doctrineSubscription->startDate = $subscription->getPeriod()->getStartDate();
        $doctrineSubscription->endDate = $subscription->getPeriod()->getEndDate();
        $doctrineSubscription->cancelledAt = $subscription->getCancelledAt();
        $doctrineSubscription->createdAt = $subscription->getCreatedAt();

        return $doctrineSubscription;
    }

    public function toDomain(): Subscription
    {
        $period = Period::create($this->startDate, $this->endDate);
        $subscription = Subscription::create(
            SubscriptionId::fromString($this->id),
            UserId::fromString($this->userId),
            ProductId::fromString($this->productId),
            $this->pricingOptionName,
            $period
        );

        // Use reflection to set cancelledAt and createdAt
        $reflection = new \ReflectionClass($subscription);

        if ($this->cancelledAt !== null) {
            $property = $reflection->getProperty('cancelledAt');
            $property->setAccessible(true);
            $property->setValue($subscription, $this->cancelledAt);
        }

        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($subscription, $this->createdAt);

        return $subscription;
    }

    public function updateFromDomain(Subscription $subscription): void
    {
        $this->pricingOptionName = $subscription->getPricingOptionName();
        $this->startDate = $subscription->getPeriod()->getStartDate();
        $this->endDate = $subscription->getPeriod()->getEndDate();
        $this->cancelledAt = $subscription->getCancelledAt();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
