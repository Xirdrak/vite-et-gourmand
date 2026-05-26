<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findActifs(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.actif = true')
            ->join('m.theme', 't')
            ->join('m.regime', 'r')
            ->orderBy('t.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
