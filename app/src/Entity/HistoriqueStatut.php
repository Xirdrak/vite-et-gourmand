<?php

namespace App\Entity;

use App\Enum\CommandeStatut;
use App\Repository\HistoriqueStatutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueStatutRepository::class)]
#[ORM\Table(name: 'historique_statut')]
class HistoriqueStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'historique_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'historiqueStatuts')]
    #[ORM\JoinColumn(name: 'commande_id', referencedColumnName: 'commande_id', nullable: false, onDelete: 'CASCADE')]
    private Commande $commande;

    #[ORM\Column(length: 50)]
    private string $statut;

    #[ORM\Column(name: 'date_heure', type: 'datetime')]
    private \DateTimeInterface $dateHeure;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->dateHeure = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getCommande(): Commande { return $this->commande; }
    public function setCommande(Commande $commande): static { $this->commande = $commande; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDateHeure(): \DateTimeInterface { return $this->dateHeure; }
    public function setDateHeure(\DateTimeInterface $d): static { $this->dateHeure = $d; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $c): static { $this->commentaire = $c; return $this; }

    public function getStatutLabel(): string
    {
        $enum = CommandeStatut::tryFrom($this->statut);
        return $enum ? $enum->label() : $this->statut;
    }
}
