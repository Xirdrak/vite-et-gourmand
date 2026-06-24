<?php

namespace App\Command;

use App\Repository\CommandeRepository;
use App\Service\StatistiquesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stats:sync',
    description: 'Synchronise les commandes MySQL vers MongoDB pour le dashboard statistiques',
)]
class SyncStatsCommand extends Command
{
    public function __construct(
        private CommandeRepository $commandeRepository,
        private StatistiquesService $statistiques,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->statistiques->estDisponible()) {
            $io->error('Impossible de joindre MongoDB. Verifie MONGODB_URI et l\'acces reseau (Atlas Network Access).');
            return Command::FAILURE;
        }

        $commandes = $this->commandeRepository->findAllWithMenu();
        $nombre = $this->statistiques->synchroniser($commandes);

        $io->success(sprintf('%d commande(s) synchronisee(s) vers MongoDB.', $nombre));

        return Command::SUCCESS;
    }
}
