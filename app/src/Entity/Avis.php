<?php

namespace App\Entity;

use App\Enum\AvisStatut;
use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\Table(name: 'avis')]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'avis_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id', nullable: false)]
    private Utilisateur $utilisateur;

    #[ORM\OneToOne(targetEntity: Commande::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(name: 'commande_id', referencedColumnName: 'commande_id', nullable: false, unique: true)]
    private Commande $commande;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private int $note;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(enumType: AvisStatut::class)]
    private AvisStatut $statut = AvisStatut::EnAttente;

    #[ORM\Column(name: 'date_avis', type: 'datetime')]
    private \DateTimeInterface $dateAvis;

    public function __construct()
    {
        $this->dateAvis = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $u): static { $this->utilisateur = $u; return $this; }
    public function getCommande(): Commande { return $this->commande; }
    public function setCommande(Commande $c): static { $this->commande = $c; return $this; }
    public function getNote(): int { return $this->note; }
    public function setNote(int $note): static { $this->note = $note; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $d): static { $this->description = $d; return $this; }
    public function getStatut(): AvisStatut { return $this->statut; }
    public function setStatut(AvisStatut $statut): static { $this->statut = $statut; return $this; }
    public function getDateAvis(): \DateTimeInterface { return $this->dateAvis; }
    public function setDateAvis(\DateTimeInterface $d): static { $this->dateAvis = $d; return $this; }
}
