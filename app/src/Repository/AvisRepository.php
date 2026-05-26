<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Enum\AvisStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function findValides(): array
    {
        return $this->findBy(['statut' => AvisStatut::Valide], ['dateAvis' => 'DESC']);
    }

    public function findEnAttente(): array
    {
        return $this->findBy(['statut' => AvisStatut::EnAttente], ['dateAvis' => 'ASC']);
    }
}
