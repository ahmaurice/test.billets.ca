<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\UseCase\CancelSubscription\CancelSubscriptionCommand;
use App\Application\UseCase\CancelSubscription\CancelSubscriptionHandler;
use App\Domain\Entity\Subscription;
use App\Domain\Exception\SubscriptionNotFoundException;
use App\Domain\ValueObject\Period;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Repository\InMemorySubscriptionRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CancelSubscriptionHandlerTest extends TestCase
{
    private InMemorySubscriptionRepository $subscriptionRepository;
    private CancelSubscriptionHandler $handler;

    protected function setUp(): void
    {
        $this->subscriptionRepository = new InMemorySubscriptionRepository();
        $this->handler = new CancelSubscriptionHandler($this->subscriptionRepository);
    }

    public function testCancelSubscriptionSuccessfully(): void
    {
        $subscriptionId = SubscriptionId::generate();
        $startDate = new DateTimeImmutable();
        $endDate = $startDate->modify('+1 month');
        $period = Period::create($startDate, $endDate);

        $subscription = Subscription::create(
            $subscriptionId,
            UserId::generate(),
            ProductId::generate(),
            'Monthly',
            $period
        );
        $this->subscriptionRepository->save($subscription);

        $command = new CancelSubscriptionCommand($subscriptionId->toString());

        $this->handler->handle($command);

        $cancelledSubscription = $this->subscriptionRepository->findById($subscriptionId);
        $this->assertTrue($cancelledSubscription->isCancelled());
    }

    public function testCancelNonExistentSubscription(): void
    {
        $command = new CancelSubscriptionCommand('non-existent-subscription');

        $this->expectException(SubscriptionNotFoundException::class);

        $this->handler->handle($command);
    }
}
