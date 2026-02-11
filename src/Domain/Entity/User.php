<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class User
{
    private DateTimeImmutable $createdAt;

    private function __construct(
        private UserId $id,
        private string $name,
        private Email $email
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(UserId $id, string $name, Email $email): self
    {
        return new self($id, $name, $email);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }
}
