<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(User $user): void
    {
        $doctrineUser = $this->entityManager
            ->getRepository(DoctrineUser::class)
            ->findOneBy(['id' => $user->getId()->toString()]);

        if ($doctrineUser === null) {
            $doctrineUser = DoctrineUser::fromDomain($user);
            $this->entityManager->persist($doctrineUser);
        } else {
            $doctrineUser->updateFromDomain($user);
        }

        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?User
    {
        $doctrineUser = $this->entityManager
            ->getRepository(DoctrineUser::class)
            ->findOneBy(['id' => $id->toString()]);

        return $doctrineUser?->toDomain();
    }

    public function findAll(): array
    {
        $doctrineUsers = $this->entityManager
            ->getRepository(DoctrineUser::class)
            ->findAll();

        return array_map(
            fn(DoctrineUser $doctrineUser) => $doctrineUser->toDomain(),
            $doctrineUsers
        );
    }
}
