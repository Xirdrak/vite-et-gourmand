<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
#[ORM\Table(name: 'horaire')]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'horaire_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $jour;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $heureOuverture;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $heureFermeture;

    public function getId(): ?int { return $this->id; }
    public function getJour(): string { return $this->jour; }
    public function setJour(string $jour): static { $this->jour = $jour; return $this; }
    public function getHeureOuverture(): \DateTimeInterface { return $this->heureOuverture; }
    public function setHeureOuverture(\DateTimeInterface $h): static { $this->heureOuverture = $h; return $this; }
    public function getHeureFermeture(): \DateTimeInterface { return $this->heureFermeture; }
    public function setHeureFermeture(\DateTimeInterface $h): static { $this->heureFermeture = $h; return $this; }
}
