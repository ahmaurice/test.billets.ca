<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Entity\PricingOption;
use App\Domain\Entity\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class DoctrineProduct
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'json')]
    private array $pricingOptions = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(Product $product): self
    {
        $doctrineProduct = new self();
        $doctrineProduct->id = $product->getId()->toString();
        $doctrineProduct->name = $product->getName();
        $doctrineProduct->description = $product->getDescription();
        $doctrineProduct->createdAt = $product->getCreatedAt();

        // Serialize pricing options
        $doctrineProduct->pricingOptions = array_map(
            fn(PricingOption $option) => [
                'name' => $option->getName(),
                'price' => $option->getPrice()->getAmount(),
                'currency' => $option->getPrice()->getCurrency(),
                'duration' => $option->getDurationInMonths(),
            ],
            $product->getPricingOptions()
        );

        return $doctrineProduct;
    }

    public function toDomain(): Product
    {
        $product = Product::create(
            ProductId::fromString($this->id),
            $this->name,
            $this->description
        );

        // Restore pricing options
        foreach ($this->pricingOptions as $optionData) {
            $product->addPricingOption(
                PricingOption::create(
                    $optionData['name'],
                    Money::create($optionData['price'], $optionData['currency']),
                    $optionData['duration']
                )
            );
        }

        // Use reflection to set createdAt
        $reflection = new \ReflectionClass($product);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($product, $this->createdAt);

        return $product;
    }

    public function updateFromDomain(Product $product): void
    {
        $this->name = $product->getName();
        $this->description = $product->getDescription();
        $this->pricingOptions = array_map(
            fn(PricingOption $option) => [
                'name' => $option->getName(),
                'price' => $option->getPrice()->getAmount(),
                'currency' => $option->getPrice()->getCurrency(),
                'duration' => $option->getDurationInMonths(),
            ],
            $product->getPricingOptions()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }
}
