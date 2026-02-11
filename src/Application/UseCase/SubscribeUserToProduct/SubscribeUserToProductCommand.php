<?php

declare(strict_types=1);

namespace App\Application\UseCase\SubscribeUserToProduct;

final readonly class SubscribeUserToProductCommand
{
    public function __construct(
        public string $userId,
        public string $productId,
        public string $pricingOptionName
    ) {
    }
}
