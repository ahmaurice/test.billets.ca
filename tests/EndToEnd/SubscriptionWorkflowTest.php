<?php

declare(strict_types=1);

namespace App\Tests\EndToEnd;

use App\Application\UseCase\CancelSubscription\CancelSubscriptionCommand;
use App\Application\UseCase\CancelSubscription\CancelSubscriptionHandler;
use App\Application\UseCase\CreateProduct\CreateProductCommand;
use App\Application\UseCase\CreateProduct\CreateProductHandler;
use App\Application\UseCase\CreateUser\CreateUserCommand;
use App\Application\UseCase\CreateUser\CreateUserHandler;
use App\Application\UseCase\ListActiveSubscriptions\ListActiveSubscriptionsHandler;
use App\Application\UseCase\ListActiveSubscriptions\ListActiveSubscriptionsQuery;
use App\Application\UseCase\SubscribeUserToProduct\SubscribeUserToProductCommand;
use App\Application\UseCase\SubscribeUserToProduct\SubscribeUserToProductHandler;
use App\Infrastructure\Repository\InMemoryProductRepository;
use App\Infrastructure\Repository\InMemorySubscriptionRepository;
use App\Infrastructure\Repository\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * End-to-End Fuzzy Test - Simulates a complete subscription workflow
 * with randomized data and multiple scenarios
 */
final class SubscriptionWorkflowTest extends TestCase
{
    private InMemoryUserRepository $userRepository;
    private InMemoryProductRepository $productRepository;
    private InMemorySubscriptionRepository $subscriptionRepository;

    protected function setUp(): void
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->productRepository = new InMemoryProductRepository();
        $this->subscriptionRepository = new InMemorySubscriptionRepository();
    }

    /**
     * @dataProvider workflowDataProvider
     */
    public function testCompleteSubscriptionWorkflow(
        array $users,
        array $products,
        array $subscriptions
    ): void {
        // Phase 1: Create Users
        $createdUserIds = [];
        $createUserHandler = new CreateUserHandler($this->userRepository);

        foreach ($users as $userData) {
            $command = new CreateUserCommand($userData['name'], $userData['email']);
            $userId = $createUserHandler->handle($command);
            $createdUserIds[] = $userId->toString();
        }

        $this->assertCount(count($users), $createdUserIds);

        // Phase 2: Create Products with Pricing Options
        $createdProductIds = [];
        $createProductHandler = new CreateProductHandler($this->productRepository);

        foreach ($products as $productData) {
            $command = new CreateProductCommand(
                $productData['name'],
                $productData['description'],
                $productData['pricing']
            );
            $productId = $createProductHandler->handle($command);
            $createdProductIds[] = $productId->toString();
        }

        $this->assertCount(count($products), $createdProductIds);

        // Phase 3: Create Subscriptions
        $createdSubscriptionIds = [];
        $subscribeHandler = new SubscribeUserToProductHandler(
            $this->userRepository,
            $this->productRepository,
            $this->subscriptionRepository
        );

        foreach ($subscriptions as $subData) {
            $userId = $createdUserIds[$subData['userIndex']];
            $productId = $createdProductIds[$subData['productIndex']];

            $command = new SubscribeUserToProductCommand(
                $userId,
                $productId,
                $subData['pricingOption']
            );
            $subscriptionId = $subscribeHandler->handle($command);
            $createdSubscriptionIds[] = $subscriptionId->toString();
        }

        $this->assertCount(count($subscriptions), $createdSubscriptionIds);

        // Phase 4: List Active Subscriptions for each user
        $listHandler = new ListActiveSubscriptionsHandler(
            $this->userRepository,
            $this->subscriptionRepository,
            $this->productRepository
        );

        foreach ($createdUserIds as $userId) {
            $query = new ListActiveSubscriptionsQuery($userId);
            $activeSubscriptions = $listHandler->handle($query);

            $this->assertIsArray($activeSubscriptions);
            foreach ($activeSubscriptions as $subDTO) {
                $this->assertNotEmpty($subDTO->id);
                $this->assertNotEmpty($subDTO->productName);
                $this->assertFalse($subDTO->isCancelled);
            }
        }

        // Phase 5: Cancel some subscriptions randomly
        $cancelHandler = new CancelSubscriptionHandler($this->subscriptionRepository);
        $subscriptionsToCancelCount = min(2, count($createdSubscriptionIds));

        for ($i = 0; $i < $subscriptionsToCancelCount; $i++) {
            $command = new CancelSubscriptionCommand($createdSubscriptionIds[$i]);
            $cancelHandler->handle($command);
        }

        // Phase 6: Verify cancelled subscriptions are marked correctly
        foreach ($createdUserIds as $userId) {
            $query = new ListActiveSubscriptionsQuery($userId);
            $activeSubscriptions = $listHandler->handle($query);

            foreach ($activeSubscriptions as $subDTO) {
                $wasCancelled = in_array($subDTO->id, array_slice($createdSubscriptionIds, 0, $subscriptionsToCancelCount));
                $this->assertEquals($wasCancelled, $subDTO->isCancelled);
            }
        }

        // Phase 7: Verify repository state
        $allUsers = $this->userRepository->findAll();
        $allProducts = $this->productRepository->findAll();
        $allSubscriptions = $this->subscriptionRepository->findAll();

        $this->assertCount(count($users), $allUsers);
        $this->assertCount(count($products), $allProducts);
        $this->assertCount(count($subscriptions), $allSubscriptions);
    }

    public static function workflowDataProvider(): array
    {
        return [
            'Scenario 1: Basic workflow with 2 users and 2 products' => [
                'users' => [
                    ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
                    ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
                ],
                'products' => [
                    [
                        'name' => 'Premium SaaS',
                        'description' => 'Premium software subscription',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 29.99, 'currency' => 'USD', 'duration' => 1],
                            ['name' => 'Annual', 'price' => 299.99, 'currency' => 'USD', 'duration' => 12],
                        ],
                    ],
                    [
                        'name' => 'Cloud Storage',
                        'description' => 'Secure cloud storage',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 9.99, 'currency' => 'USD', 'duration' => 1],
                        ],
                    ],
                ],
                'subscriptions' => [
                    ['userIndex' => 0, 'productIndex' => 0, 'pricingOption' => 'Monthly'],
                    ['userIndex' => 0, 'productIndex' => 1, 'pricingOption' => 'Monthly'],
                    ['userIndex' => 1, 'productIndex' => 0, 'pricingOption' => 'Annual'],
                ],
            ],
            'Scenario 2: Multiple subscriptions with varied pricing' => [
                'users' => [
                    ['name' => 'Charlie Davis', 'email' => 'charlie@example.com'],
                    ['name' => 'Diana Evans', 'email' => 'diana@example.com'],
                    ['name' => 'Ethan Foster', 'email' => 'ethan@example.com'],
                ],
                'products' => [
                    [
                        'name' => 'Video Streaming',
                        'description' => 'HD video streaming service',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 14.99, 'currency' => 'EUR', 'duration' => 1],
                            ['name' => 'Quarterly', 'price' => 39.99, 'currency' => 'EUR', 'duration' => 3],
                            ['name' => 'Annual', 'price' => 149.99, 'currency' => 'EUR', 'duration' => 12],
                        ],
                    ],
                    [
                        'name' => 'Music Streaming',
                        'description' => 'Unlimited music streaming',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 9.99, 'currency' => 'EUR', 'duration' => 1],
                            ['name' => 'Annual', 'price' => 99.99, 'currency' => 'EUR', 'duration' => 12],
                        ],
                    ],
                ],
                'subscriptions' => [
                    ['userIndex' => 0, 'productIndex' => 0, 'pricingOption' => 'Monthly'],
                    ['userIndex' => 1, 'productIndex' => 0, 'pricingOption' => 'Quarterly'],
                    ['userIndex' => 1, 'productIndex' => 1, 'pricingOption' => 'Annual'],
                    ['userIndex' => 2, 'productIndex' => 1, 'pricingOption' => 'Monthly'],
                ],
            ],
            'Scenario 3: Single user with multiple subscriptions' => [
                'users' => [
                    ['name' => 'Frank Green', 'email' => 'frank@example.com'],
                ],
                'products' => [
                    [
                        'name' => 'Fitness App',
                        'description' => 'Personal fitness tracking',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 12.99, 'currency' => 'GBP', 'duration' => 1],
                        ],
                    ],
                    [
                        'name' => 'Meditation App',
                        'description' => 'Guided meditation sessions',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 7.99, 'currency' => 'GBP', 'duration' => 1],
                        ],
                    ],
                    [
                        'name' => 'Nutrition Tracker',
                        'description' => 'Track your meals and calories',
                        'pricing' => [
                            ['name' => 'Monthly', 'price' => 5.99, 'currency' => 'GBP', 'duration' => 1],
                        ],
                    ],
                ],
                'subscriptions' => [
                    ['userIndex' => 0, 'productIndex' => 0, 'pricingOption' => 'Monthly'],
                    ['userIndex' => 0, 'productIndex' => 1, 'pricingOption' => 'Monthly'],
                    ['userIndex' => 0, 'productIndex' => 2, 'pricingOption' => 'Monthly'],
                ],
            ],
        ];
    }
}
