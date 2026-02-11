<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\ProductId;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    /** @var array<string, Product> */
    private array $products = [];

    public function save(Product $product): void
    {
        $this->products[$product->getId()->toString()] = $product;
    }

    public function findById(ProductId $id): ?Product
    {
        return $this->products[$id->toString()] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->products);
    }

    public function clear(): void
    {
        $this->products = [];
    }
}
