<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class DoctrineUser
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(User $user): self
    {
        $doctrineUser = new self();
        $doctrineUser->id = $user->getId()->toString();
        $doctrineUser->name = $user->getName();
        $doctrineUser->email = $user->getEmail()->toString();
        $doctrineUser->createdAt = $user->getCreatedAt();

        return $doctrineUser;
    }

    public function toDomain(): User
    {
        $user = User::create(
            UserId::fromString($this->id),
            $this->name,
            Email::fromString($this->email)
        );

        // Use reflection to set createdAt (readonly property)
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($user, $this->createdAt);

        return $user;
    }

    public function updateFromDomain(User $user): void
    {
        $this->name = $user->getName();
        $this->email = $user->getEmail()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }
}
