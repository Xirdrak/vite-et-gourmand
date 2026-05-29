<?php

namespace App\Service;

use App\Entity\ResetPasswordRequest;
use App\Entity\Utilisateur;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class ResetPasswordService
{
    // Duree de validite du token en secondes (1 heure)
    private const TTL = 3600;

    public function __construct(
        private EntityManagerInterface $em,
        private ResetPasswordRequestRepository $repository,
    ) {}

    public function generateToken(Utilisateur $utilisateur): string
    {
        // Supprime les anciens tokens de cet utilisateur
        $this->repository->deleteByUtilisateur($utilisateur);

        $selector = bin2hex(random_bytes(8));   // 16 chars
        $verifier = bin2hex(random_bytes(32));  // 64 chars
        $fullToken = $selector . $verifier;

        $request = new ResetPasswordRequest();
        $request->setUtilisateur($utilisateur);
        $request->setSelector($selector);
        $request->setHashedToken(hash('sha256', $verifier));
        $request->setRequestedAt(new \DateTime());
        $request->setExpiresAt(new \DateTime('+' . self::TTL . ' seconds'));

        $this->em->persist($request);
        $this->em->flush();

        return $fullToken;
    }

    public function validateToken(string $fullToken): ?Utilisateur
    {
        if (strlen($fullToken) !== 80) {
            return null;
        }

        $selector = substr($fullToken, 0, 16);
        $verifier = substr($fullToken, 16);

        $request = $this->repository->findOneBy(['selector' => $selector]);

        if (!$request) {
            return null;
        }

        if ($request->isExpired()) {
            $this->em->remove($request);
            $this->em->flush();
            return null;
        }

        if (!hash_equals($request->getHashedToken(), hash('sha256', $verifier))) {
            return null;
        }

        return $request->getUtilisateur();
    }

    public function consumeToken(string $fullToken): void
    {
        $selector = substr($fullToken, 0, 16);
        $request = $this->repository->findOneBy(['selector' => $selector]);

        if ($request) {
            $this->em->remove($request);
            $this->em->flush();
        }
    }
}
