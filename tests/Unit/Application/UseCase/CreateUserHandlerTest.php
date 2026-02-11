<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase;

use App\Application\UseCase\CreateUser\CreateUserCommand;
use App\Application\UseCase\CreateUser\CreateUserHandler;
use App\Infrastructure\Repository\InMemoryUserRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;
    private CreateUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->handler = new CreateUserHandler($this->userRepository);
    }

    public function testCreateUserSuccessfully(): void
    {
        $command = new CreateUserCommand('Maurice AHOUMENOU', 'maurice@dot.com');

        $userId = $this->handler->handle($command);

        $this->assertNotNull($userId);
        $user = $this->userRepository->findById($userId);
        $this->assertNotNull($user);
        $this->assertEquals('Maurice AHOUMENOU', $user->getName());
        $this->assertEquals('maurice@dot.com', $user->getEmail()->toString());
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        $command = new CreateUserCommand('Maurice AHOUMENOU', 'invalid-email');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address');

        $this->handler->handle($command);
    }
}
