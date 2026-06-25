<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\HistoriqueStatut;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use App\Enum\CommandeStatut;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommandeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommandeRepository $commandeRepository,
    ) {}

    public function calculerPrixMenu(Menu $menu, int $nombrePersonnes): float
    {
        $prix = (float) $menu->getPrixParPersonne() * $nombrePersonnes;

        if ($nombrePersonnes >= $menu->getNombrePersonneMinimum() + 5) {
            $prix *= 0.9;
        }

        return round($prix, 2);
    }

    public function calculerPrixLivraison(bool $horsBordeaux, int $nombreKm = 0): float
    {
        if (!$horsBordeaux) {
            return 0.0;
        }

        return round(5.0 + (0.59 * $nombreKm), 2);
    }

    public function genererNumeroCommande(): string
    {
        $annee = date('Y');
        $derniere = $this->commandeRepository->trouverDerniereCommande($annee);

        $numero = 1;
        if ($derniere !== null) {
            $parties = explode('-', $derniere->getNumeroCommande());
            $numero = (int) end($parties) + 1;
        }

        return sprintf('VG-%s-%05d', $annee, $numero);
    }

    public function creerCommande(
        Utilisateur $utilisateur,
        Menu $menu,
        array $data,
    ): Commande {
        $horsBordeaux = $data['hors_bordeaux'] ?? false;
        $nombreKm     = $horsBordeaux ? (int) ($data['nombre_km'] ?? 0) : 0;

        $prixMenu      = $this->calculerPrixMenu($menu, (int) $data['nombre_personne']);
        $prixLivraison = $this->calculerPrixLivraison($horsBordeaux, $nombreKm);
        $prixTotal     = round($prixMenu + $prixLivraison, 2);

        $commande = new Commande();
        $commande->setNumeroCommande($this->genererNumeroCommande());
        $commande->setUtilisateur($utilisateur);
        $commande->setMenu($menu);
        $commande->setDatePrestation($data['date_prestation']);
        $commande->setHeureLivraison($data['heure_livraison']);
        $commande->setAdresseLivraison($data['adresse_livraison']);
        $commande->setVilleLivraison($data['ville_livraison']);
        $commande->setNombrePersonne((int) $data['nombre_personne']);
        $commande->setPrixMenu((string) $prixMenu);
        $commande->setPrixLivraison((string) $prixLivraison);
        $commande->setPrixTotal((string) $prixTotal);
        $commande->setStatut(CommandeStatut::Nouvelle);

        $historique = new HistoriqueStatut();
        $historique->setCommande($commande);
        $historique->setStatut(CommandeStatut::Nouvelle->value);
        $commande->getHistoriqueStatuts()->add($historique);

        $menu->setQuantiteRestante($menu->getQuantiteRestante() - 1);

        $this->em->persist($commande);
        $this->em->persist($historique);
        $this->em->flush();

        return $commande;
    }

    public function modifierCommande(Commande $commande, array $data): void
    {
        $menu          = $commande->getMenu();
        $horsBordeaux  = $data['hors_bordeaux'] ?? false;
        $nombreKm      = $horsBordeaux ? (int) ($data['nombre_km'] ?? 0) : 0;
        $nombrePersonnes = (int) $data['nombre_personne'];

        $prixMenu      = $this->calculerPrixMenu($menu, $nombrePersonnes);
        $prixLivraison = $this->calculerPrixLivraison($horsBordeaux, $nombreKm);

        $commande->setDatePrestation($data['date_prestation']);
        $commande->setHeureLivraison($data['heure_livraison']);
        $commande->setAdresseLivraison($data['adresse_livraison']);
        $commande->setVilleLivraison($data['ville_livraison']);
        $commande->setNombrePersonne($nombrePersonnes);
        $commande->setPrixMenu((string) $prixMenu);
        $commande->setPrixLivraison((string) $prixLivraison);
        $commande->setPrixTotal((string) round($prixMenu + $prixLivraison, 2));

        $this->em->flush();
    }

    public function annulerCommande(Commande $commande): void
    {
        $commande->setStatut(CommandeStatut::Annulee);

        $historique = new HistoriqueStatut();
        $historique->setCommande($commande);
        $historique->setStatut(CommandeStatut::Annulee->value);
        $commande->getHistoriqueStatuts()->add($historique);

        $menu = $commande->getMenu();
        $menu->setQuantiteRestante($menu->getQuantiteRestante() + 1);

        $this->em->persist($historique);
        $this->em->flush();
    }
}
