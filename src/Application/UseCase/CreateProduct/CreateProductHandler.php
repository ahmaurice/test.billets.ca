<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateProduct;

use App\Domain\Entity\PricingOption;
use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;

final readonly class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    public function handle(CreateProductCommand $command): ProductId
    {
        $productId = ProductId::generate();

        $product = Product::create(
            $productId,
            $command->name,
            $command->description
        );

        foreach ($command->pricingOptions as $option) {
            $pricingOption = PricingOption::create(
                $option['name'],
                Money::create($option['price'], $option['currency']),
                $option['duration']
            );
            $product->addPricingOption($pricingOption);
        }

        $this->productRepository->save($product);

        return $productId;
    }
}
