<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

final class ProductNotFoundException extends Exception
{
    public static function withId(string $id): self
    {
        return new self("Product with ID '{$id}' not found");
    }
}
