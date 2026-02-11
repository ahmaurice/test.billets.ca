<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[$user->getId()->toString()] = $user;
    }

    public function findById(UserId $id): ?User
    {
        return $this->users[$id->toString()] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function clear(): void
    {
        $this->users = [];
    }
}
