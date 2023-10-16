<?php

namespace App\Command;

use App\DataFixtures\PanierFixtures;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadPanierFixturesCommand extends Command
{
    private $entityManager;
    private $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:load-panier-fixtures')
            ->setDescription('Load Panier fixtures.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Loading Panier fixtures...',
            '==========================',
            '',
        ]);

        // Load Panier fixtures
        $panierFixtures = new PanierFixtures($this->productRepository);
        $panierFixtures->load($this->entityManager);

        $output->writeln('Panier fixtures loaded.');

        return Command::SUCCESS;
    }
}
