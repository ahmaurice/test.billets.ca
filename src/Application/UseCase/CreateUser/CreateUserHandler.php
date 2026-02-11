<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateUser;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(CreateUserCommand $command): UserId
    {
        $userId = UserId::generate();
        $email = Email::fromString($command->email);

        $user = User::create($userId, $command->name, $email);

        $this->userRepository->save($user);

        return $userId;
    }
}
