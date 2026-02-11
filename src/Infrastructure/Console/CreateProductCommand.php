<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\UseCase\CreateProduct\CreateProductCommand as CreateProductUseCase;
use App\Application\UseCase\CreateProduct\CreateProductHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-product',
    description: 'Create a new product with pricing options'
)]
final class CreateProductCommand extends Command
{
    public function __construct(
        private readonly CreateProductHandler $handler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Product name')
            ->addArgument('description', InputArgument::REQUIRED, 'Product description')
            ->addOption(
                'pricing',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Pricing option in format: name:price:currency:duration (e.g., Monthly:9.99:USD:1)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $pricingOptions = [];
            foreach ($input->getOption('pricing') as $pricingString) {
                $parts = explode(':', $pricingString);
                if (count($parts) !== 4) {
                    $io->error("Invalid pricing format: {$pricingString}");
                    return Command::FAILURE;
                }

                $pricingOptions[] = [
                    'name' => $parts[0],
                    'price' => (float) $parts[1],
                    'currency' => $parts[2],
                    'duration' => (int) $parts[3],
                ];
            }

            $command = new CreateProductUseCase(
                $input->getArgument('name'),
                $input->getArgument('description'),
                $pricingOptions
            );

            $productId = $this->handler->handle($command);

            $io->success("Product created successfully with ID: {$productId->toString()}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to create product: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
