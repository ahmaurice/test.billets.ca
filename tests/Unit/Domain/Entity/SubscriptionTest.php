<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Subscription;
use App\Domain\ValueObject\Period;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SubscriptionTest extends TestCase
{
    public function testSubscriptionCanBeCreated(): void
    {
        $subscriptionId = SubscriptionId::generate();
        $userId = UserId::generate();
        $productId = ProductId::generate();
        $startDate = new DateTimeImmutable();
        $endDate = $startDate->modify('+1 month');
        $period = Period::create($startDate, $endDate);

        $subscription = Subscription::create(
            $subscriptionId,
            $userId,
            $productId,
            'Monthly',
            $period
        );

        $this->assertEquals($subscriptionId, $subscription->getId());
        $this->assertEquals($userId, $subscription->getUserId());
        $this->assertEquals($productId, $subscription->getProductId());
        $this->assertEquals('Monthly', $subscription->getPricingOptionName());
        $this->assertFalse($subscription->isCancelled());
    }

    public function testSubscriptionCanBeCancelled(): void
    {
        $subscription = $this->createSubscription();

        $this->assertFalse($subscription->isCancelled());

        $subscription->cancel();

        $this->assertTrue($subscription->isCancelled());
        $this->assertInstanceOf(DateTimeImmutable::class, $subscription->getCancelledAt());
    }

    public function testCancelledSubscriptionCannotBeCancelledAgain(): void
    {
        $subscription = $this->createSubscription();
        $subscription->cancel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription is already cancelled');

        $subscription->cancel();
    }

    public function testSubscriptionIsActiveWithinPeriod(): void
    {
        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-02-01');
        $period = Period::create($startDate, $endDate);

        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            ProductId::generate(),
            'Monthly',
            $period
        );

        $this->assertTrue($subscription->isActive(new DateTimeImmutable('2024-01-15')));
        $this->assertFalse($subscription->isActive(new DateTimeImmutable('2024-02-02')));
    }

    public function testCancelledButStillActiveSubscription(): void
    {
        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-02-01');
        $period = Period::create($startDate, $endDate);

        $subscription = Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            ProductId::generate(),
            'Monthly',
            $period
        );

        $subscription->cancel();

        // Still active until end of period
        $this->assertTrue($subscription->isActive(new DateTimeImmutable('2024-01-15')));
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isActiveAndNotCancelled(new DateTimeImmutable('2024-01-15')));
    }

    private function createSubscription(): Subscription
    {
        $startDate = new DateTimeImmutable();
        $endDate = $startDate->modify('+1 month');
        $period = Period::create($startDate, $endDate);

        return Subscription::create(
            SubscriptionId::generate(),
            UserId::generate(),
            ProductId::generate(),
            'Monthly',
            $period
        );
    }
}
