<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResetPasswordRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function deleteByUtilisateur(\App\Entity\Utilisateur $utilisateur): void
    {
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.utilisateur = :u')
            ->setParameter('u', $utilisateur)
            ->getQuery()
            ->execute();
    }
}
