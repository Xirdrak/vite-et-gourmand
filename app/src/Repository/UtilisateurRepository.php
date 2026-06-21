<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findOneByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findAllActifs(): array
    {
        return $this->findBy(['actif' => true]);
    }

    public function findEmployes(): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.role', 'r')
            ->where('r.libelle = :libelle')
            ->setParameter('libelle', 'employe')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
