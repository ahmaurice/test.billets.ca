<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

final class SubscriptionNotFoundException extends Exception
{
    public static function withId(string $id): self
    {
        return new self("Subscription with ID '{$id}' not found");
    }
}
