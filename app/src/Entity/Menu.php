<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'menu')]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'menu_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'menus')]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'theme_id', nullable: false)]
    private Theme $theme;

    #[ORM\ManyToOne(targetEntity: Regime::class, inversedBy: 'menus')]
    #[ORM\JoinColumn(name: 'regime_id', referencedColumnName: 'regime_id', nullable: false)]
    private Regime $regime;

    #[ORM\Column(length: 150)]
    private string $titre;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(name: 'nombre_personne_minimum', type: 'integer', options: ['unsigned' => true])]
    private int $nombrePersonneMinimum;

    #[ORM\Column(name: 'prix_par_personne', type: 'decimal', precision: 8, scale: 2)]
    private string $prixParPersonne;

    #[ORM\Column(name: 'quantite_restante', type: 'integer', options: ['unsigned' => true])]
    private int $quantiteRestante = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $conditions = null;

    #[ORM\Column(type: 'boolean')]
    private bool $actif = true;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'menu', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $images;

    #[ORM\ManyToMany(targetEntity: Plat::class, inversedBy: 'menus')]
    #[ORM\JoinTable(name: 'menu_plat',
        joinColumns: [new ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'plat_id', referencedColumnName: 'plat_id', onDelete: 'CASCADE')]
    )]
    private Collection $plats;

    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'menu')]
    private Collection $commandes;

    public function __construct()
    {
        $this->images   = new ArrayCollection();
        $this->plats    = new ArrayCollection();
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTheme(): Theme { return $this->theme; }
    public function setTheme(Theme $theme): static { $this->theme = $theme; return $this; }
    public function getRegime(): Regime { return $this->regime; }
    public function setRegime(Regime $regime): static { $this->regime = $regime; return $this; }
    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getNombrePersonneMinimum(): int { return $this->nombrePersonneMinimum; }
    public function setNombrePersonneMinimum(int $n): static { $this->nombrePersonneMinimum = $n; return $this; }
    public function getPrixParPersonne(): string { return $this->prixParPersonne; }
    public function setPrixParPersonne(string $prix): static { $this->prixParPersonne = $prix; return $this; }
    public function getQuantiteRestante(): int { return $this->quantiteRestante; }
    public function setQuantiteRestante(int $q): static { $this->quantiteRestante = $q; return $this; }
    public function getConditions(): ?string { return $this->conditions; }
    public function setConditions(?string $conditions): static { $this->conditions = $conditions; return $this; }
    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }
    public function getImages(): Collection { return $this->images; }
    public function getPlats(): Collection { return $this->plats; }
    public function getCommandes(): Collection { return $this->commandes; }

    public function getPrixTotal(int $nombrePersonnes): float
    {
        $prix = (float) $this->prixParPersonne * $nombrePersonnes;
        if ($nombrePersonnes >= $this->nombrePersonneMinimum + 5) {
            $prix *= 0.9;
        }
        return round($prix, 2);
    }
}
