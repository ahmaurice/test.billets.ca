<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\ProductId;
use DateTimeImmutable;
use InvalidArgumentException;

final class Product
{
    private DateTimeImmutable $createdAt;

    /** @var array<PricingOption> */
    private array $pricingOptions = [];

    private function __construct(
        private ProductId $id,
        private string $name,
        private string $description
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(ProductId $id, string $name, string $description): self
    {
        return new self($id, $name, $description);
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function addPricingOption(PricingOption $option): void
    {
        $this->pricingOptions[] = $option;
    }

    /**
     * @return array<PricingOption>
     */
    public function getPricingOptions(): array
    {
        return $this->pricingOptions;
    }

    public function getPricingOptionByName(string $name): PricingOption
    {
        foreach ($this->pricingOptions as $option) {
            if ($option->getName() === $name) {
                return $option;
            }
        }

        throw new InvalidArgumentException("Pricing option '{$name}' not found");
    }

    public function hasPricingOptions(): bool
    {
        return count($this->pricingOptions) > 0;
    }
}
