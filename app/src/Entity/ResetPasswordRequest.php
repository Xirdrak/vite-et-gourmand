<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
#[ORM\Table(name: 'reset_password_request')]
class ResetPasswordRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id', nullable: false, onDelete: 'CASCADE')]
    private Utilisateur $utilisateur;

    #[ORM\Column(length: 20)]
    private string $selector;

    #[ORM\Column(name: 'hashed_token', length: 100)]
    private string $hashedToken;

    #[ORM\Column(name: 'requested_at', type: 'datetime')]
    private \DateTimeInterface $requestedAt;

    #[ORM\Column(name: 'expires_at', type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    public function getId(): ?int { return $this->id; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $u): static { $this->utilisateur = $u; return $this; }
    public function getSelector(): string { return $this->selector; }
    public function setSelector(string $s): static { $this->selector = $s; return $this; }
    public function getHashedToken(): string { return $this->hashedToken; }
    public function setHashedToken(string $t): static { $this->hashedToken = $t; return $this; }
    public function getRequestedAt(): \DateTimeInterface { return $this->requestedAt; }
    public function setRequestedAt(\DateTimeInterface $d): static { $this->requestedAt = $d; return $this; }
    public function getExpiresAt(): \DateTimeInterface { return $this->expiresAt; }
    public function setExpiresAt(\DateTimeInterface $d): static { $this->expiresAt = $d; return $this; }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTime();
    }
}
