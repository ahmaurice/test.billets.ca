<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\UseCase\ListActiveSubscriptions\ListActiveSubscriptionsHandler;
use App\Application\UseCase\ListActiveSubscriptions\ListActiveSubscriptionsQuery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-active-subscriptions',
    description: 'List active subscriptions for a user'
)]
final class ListActiveSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly ListActiveSubscriptionsHandler $handler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user-id', InputArgument::REQUIRED, 'User ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $query = new ListActiveSubscriptionsQuery(
                $input->getArgument('user-id')
            );

            $subscriptions = $this->handler->handle($query);

            if (empty($subscriptions)) {
                $io->info('No active subscriptions found');
                return Command::SUCCESS;
            }

            $rows = [];
            foreach ($subscriptions as $sub) {
                $rows[] = [
                    $sub->id,
                    $sub->productName,
                    $sub->pricingOption,
                    $sub->startDate->format('Y-m-d H:i:s'),
                    $sub->endDate->format('Y-m-d H:i:s'),
                    $sub->isCancelled ? 'Yes' : 'No',
                ];
            }

            $io->table(
                ['ID', 'Product', 'Pricing Option', 'Start Date', 'End Date', 'Cancelled'],
                $rows
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to list subscriptions: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
