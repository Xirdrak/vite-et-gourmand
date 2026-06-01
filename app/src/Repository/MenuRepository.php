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

    public function findOneWithDetails(int $id): ?Menu
    {
        return $this->createQueryBuilder('m')
            ->where('m.id = :id')
            ->andWhere('m.actif = true')
            ->setParameter('id', $id)
            ->join('m.theme', 't')
            ->addSelect('t')
            ->join('m.regime', 'r')
            ->addSelect('r')
            ->leftJoin('m.images', 'i')
            ->addSelect('i')
            ->leftJoin('m.plats', 'p')
            ->addSelect('p')
            ->leftJoin('p.allergenes', 'a')
            ->addSelect('a')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
