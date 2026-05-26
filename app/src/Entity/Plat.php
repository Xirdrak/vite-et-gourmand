<?php

namespace App\Entity;

use App\Enum\PlatType;
use App\Repository\PlatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatRepository::class)]
#[ORM\Table(name: 'plat')]
class Plat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'plat_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(name: 'titre_plat', length: 150)]
    private string $titrePlat;

    #[ORM\Column(name: 'type_plat', enumType: PlatType::class)]
    private PlatType $typePlat;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'plats')]
    private Collection $menus;

    #[ORM\ManyToMany(targetEntity: Allergene::class, inversedBy: 'plats')]
    #[ORM\JoinTable(name: 'plat_allergene',
        joinColumns: [new ORM\JoinColumn(name: 'plat_id', referencedColumnName: 'plat_id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'allergene_id', referencedColumnName: 'allergene_id', onDelete: 'CASCADE')]
    )]
    private Collection $allergenes;

    public function __construct()
    {
        $this->menus     = new ArrayCollection();
        $this->allergenes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitrePlat(): string { return $this->titrePlat; }
    public function setTitrePlat(string $titre): static { $this->titrePlat = $titre; return $this; }
    public function getTypePlat(): PlatType { return $this->typePlat; }
    public function setTypePlat(PlatType $type): static { $this->typePlat = $type; return $this; }
    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }
    public function getMenus(): Collection { return $this->menus; }
    public function getAllergenes(): Collection { return $this->allergenes; }
}
