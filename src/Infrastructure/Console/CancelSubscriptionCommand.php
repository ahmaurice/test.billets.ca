<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\UseCase\CancelSubscription\CancelSubscriptionCommand as CancelUseCase;
use App\Application\UseCase\CancelSubscription\CancelSubscriptionHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cancel-subscription',
    description: 'Cancel a subscription'
)]
final class CancelSubscriptionCommand extends Command
{
    public function __construct(
        private readonly CancelSubscriptionHandler $handler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('subscription-id', InputArgument::REQUIRED, 'Subscription ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $command = new CancelUseCase(
                $input->getArgument('subscription-id')
            );

            $this->handler->handle($command);

            $io->success('Subscription cancelled successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to cancel subscription: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
