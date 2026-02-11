<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateUser;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $name,
        public string $email
    ) {
    }
}
