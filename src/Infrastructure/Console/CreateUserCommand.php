<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\UseCase\CreateUser\CreateUserCommand as CreateUserUseCase;
use App\Application\UseCase\CreateUser\CreateUserHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user'
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly CreateUserHandler $handler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'User name')
            ->addArgument('email', InputArgument::REQUIRED, 'User email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $command = new CreateUserUseCase(
                $input->getArgument('name'),
                $input->getArgument('email')
            );

            $userId = $this->handler->handle($command);

            $io->success("User created successfully with ID: {$userId->toString()}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to create user: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
