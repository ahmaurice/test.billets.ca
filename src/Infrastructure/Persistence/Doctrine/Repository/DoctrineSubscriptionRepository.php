<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\Subscription;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\ValueObject\SubscriptionId;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineSubscription;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Subscription $subscription): void
    {
        $doctrineSubscription = $this->entityManager
            ->getRepository(DoctrineSubscription::class)
            ->findOneBy(['id' => $subscription->getId()->toString()]);

        if ($doctrineSubscription === null) {
            $doctrineSubscription = DoctrineSubscription::fromDomain($subscription);
            $this->entityManager->persist($doctrineSubscription);
        } else {
            $doctrineSubscription->updateFromDomain($subscription);
        }

        $this->entityManager->flush();
    }

    public function findById(SubscriptionId $id): ?Subscription
    {
        $doctrineSubscription = $this->entityManager
            ->getRepository(DoctrineSubscription::class)
            ->findOneBy(['id' => $id->toString()]);

        return $doctrineSubscription?->toDomain();
    }

    public function findByUserId(UserId $userId): array
    {
        $doctrineSubscriptions = $this->entityManager
            ->getRepository(DoctrineSubscription::class)
            ->findBy(['userId' => $userId->toString()]);

        return array_map(
            fn(DoctrineSubscription $doctrineSubscription) => $doctrineSubscription->toDomain(),
            $doctrineSubscriptions
        );
    }

    public function findAll(): array
    {
        $doctrineSubscriptions = $this->entityManager
            ->getRepository(DoctrineSubscription::class)
            ->findAll();

        return array_map(
            fn(DoctrineSubscription $doctrineSubscription) => $doctrineSubscription->toDomain(),
            $doctrineSubscriptions
        );
    }
}
