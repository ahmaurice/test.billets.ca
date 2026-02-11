<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Product;
use App\Domain\ValueObject\ProductId;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;

    public function findById(ProductId $id): ?Product;

    /** @return array<Product> */
    public function findAll(): array;
}
