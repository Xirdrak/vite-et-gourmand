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

    public function findWithFilters(?CommandeStatut $statut, ?string $client): array
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.utilisateur', 'u')->addSelect('u')
            ->join('c.menu', 'm')->addSelect('m')
            ->orderBy('c.datePrestation', 'ASC');

        if ($statut !== null) {
            $qb->andWhere('c.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($client !== null && $client !== '') {
            $qb->andWhere('u.nom LIKE :client OR u.prenom LIKE :client OR u.email LIKE :client')
               ->setParameter('client', '%' . $client . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllWithMenu(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.menu', 'm')->addSelect('m')
            ->orderBy('c.dateCommande', 'ASC')
            ->getQuery()
            ->getResult();
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
