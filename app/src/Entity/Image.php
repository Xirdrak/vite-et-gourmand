<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: 'image')]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'image_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id', nullable: false, onDelete: 'CASCADE')]
    private Menu $menu;

    #[ORM\Column(length: 255)]
    private string $chemin;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $ordre = 0;

    public function getId(): ?int { return $this->id; }
    public function getMenu(): Menu { return $this->menu; }
    public function setMenu(Menu $menu): static { $this->menu = $menu; return $this; }
    public function getChemin(): string { return $this->chemin; }
    public function setChemin(string $chemin): static { $this->chemin = $chemin; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }
}
