<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\UseCase\SubscribeUserToProduct\SubscribeUserToProductCommand;
use App\Application\UseCase\SubscribeUserToProduct\SubscribeUserToProductHandler;
use App\Domain\Entity\PricingOption;
use App\Domain\Entity\Product;
use App\Domain\Entity\User;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Repository\InMemoryProductRepository;
use App\Infrastructure\Repository\InMemorySubscriptionRepository;
use App\Infrastructure\Repository\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class SubscribeUserToProductHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;
    private InMemoryProductRepository $productRepository;
    private InMemorySubscriptionRepository $subscriptionRepository;
    private SubscribeUserToProductHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->productRepository = new InMemoryProductRepository();
        $this->subscriptionRepository = new InMemorySubscriptionRepository();

        $this->handler = new SubscribeUserToProductHandler(
            $this->userRepository,
            $this->productRepository,
            $this->subscriptionRepository
        );
    }

    public function testSubscribeUserToProductSuccessfully(): void
    {
        $userId = UserId::generate();
        $user = User::create($userId, 'John Doe', Email::fromString('john@example.com'));
        $this->userRepository->save($user);

        $productId = ProductId::generate();
        $product = Product::create($productId, 'Premium Plan', 'Premium subscription');
        $product->addPricingOption(PricingOption::create('Monthly', Money::create(9.99, 'USD'), 1));
        $this->productRepository->save($product);

        $command = new SubscribeUserToProductCommand(
            $userId->toString(),
            $productId->toString(),
            'Monthly'
        );

        $subscriptionId = $this->handler->handle($command);

        $this->assertNotNull($subscriptionId);
        $subscription = $this->subscriptionRepository->findById($subscriptionId);
        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->getUserId()->equals($userId));
        $this->assertTrue($subscription->getProductId()->equals($productId));
    }

    public function testSubscribeWithNonExistentUser(): void
    {
        $productId = ProductId::generate();
        $product = Product::create($productId, 'Premium Plan', 'Premium subscription');
        $product->addPricingOption(PricingOption::create('Monthly', Money::create(9.99, 'USD'), 1));
        $this->productRepository->save($product);

        $command = new SubscribeUserToProductCommand(
            'non-existent-user',
            $productId->toString(),
            'Monthly'
        );

        $this->expectException(UserNotFoundException::class);

        $this->handler->handle($command);
    }

    public function testSubscribeWithNonExistentProduct(): void
    {
        $userId = UserId::generate();
        $user = User::create($userId, 'John Doe', Email::fromString('john@example.com'));
        $this->userRepository->save($user);

        $command = new SubscribeUserToProductCommand(
            $userId->toString(),
            'non-existent-product',
            'Monthly'
        );

        $this->expectException(ProductNotFoundException::class);

        $this->handler->handle($command);
    }
}
