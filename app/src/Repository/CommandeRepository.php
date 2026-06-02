<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Enum\CommandeStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.utilisateur = :user')
            ->setParameter('user', $utilisateur)
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatut(CommandeStatut $statut): array
    {
        return $this->findBy(['statut' => $statut], ['datePrestation' => 'ASC']);
    }

    public function trouverDerniereCommande(string $annee): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->where('c.numeroCommande LIKE :prefix')
            ->setParameter('prefix', 'VG-' . $annee . '-%')
            ->orderBy('c.numeroCommande', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
