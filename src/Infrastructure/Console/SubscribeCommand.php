<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\UseCase\SubscribeUserToProduct\SubscribeUserToProductCommand as SubscribeUseCase;
use App\Application\UseCase\SubscribeUserToProduct\SubscribeUserToProductHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:subscribe',
    description: 'Subscribe a user to a product'
)]
final class SubscribeCommand extends Command
{
    public function __construct(
        private readonly SubscribeUserToProductHandler $handler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user-id', InputArgument::REQUIRED, 'User ID')
            ->addArgument('product-id', InputArgument::REQUIRED, 'Product ID')
            ->addArgument('pricing-option', InputArgument::REQUIRED, 'Pricing option name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $command = new SubscribeUseCase(
                $input->getArgument('user-id'),
                $input->getArgument('product-id'),
                $input->getArgument('pricing-option')
            );

            $subscriptionId = $this->handler->handle($command);

            $io->success("Subscription created successfully with ID: {$subscriptionId->toString()}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to create subscription: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
