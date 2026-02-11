<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineProduct;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Product $product): void
    {
        $doctrineProduct = $this->entityManager
            ->getRepository(DoctrineProduct::class)
            ->findOneBy(['id' => $product->getId()->toString()]);

        if ($doctrineProduct === null) {
            $doctrineProduct = DoctrineProduct::fromDomain($product);
            $this->entityManager->persist($doctrineProduct);
        } else {
            $doctrineProduct->updateFromDomain($product);
        }

        $this->entityManager->flush();
    }

    public function findById(ProductId $id): ?Product
    {
        $doctrineProduct = $this->entityManager
            ->getRepository(DoctrineProduct::class)
            ->findOneBy(['id' => $id->toString()]);

        return $doctrineProduct?->toDomain();
    }

    public function findAll(): array
    {
        $doctrineProducts = $this->entityManager
            ->getRepository(DoctrineProduct::class)
            ->findAll();

        return array_map(
            fn(DoctrineProduct $doctrineProduct) => $doctrineProduct->toDomain(),
            $doctrineProducts
        );
    }
}
