<?php

namespace App\Entity;

use App\Enum\CommandeStatut;
use App\Enum\ModeContact;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'commande_id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_commande', length: 20, unique: true)]
    private string $numeroCommande;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id', nullable: false)]
    private Utilisateur $utilisateur;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id', nullable: false)]
    private Menu $menu;

    #[ORM\Column(name: 'date_commande', type: 'datetime')]
    private \DateTimeInterface $dateCommande;

    #[ORM\Column(name: 'date_prestation', type: 'date')]
    private \DateTimeInterface $datePrestation;

    #[ORM\Column(name: 'heure_livraison', type: 'time')]
    private \DateTimeInterface $heureLivraison;

    #[ORM\Column(name: 'adresse_livraison', length: 255)]
    private string $adresseLivraison;

    #[ORM\Column(name: 'ville_livraison', length: 100)]
    private string $villeLivraison;

    #[ORM\Column(name: 'nombre_personne', type: 'integer', options: ['unsigned' => true])]
    private int $nombrePersonne;

    #[ORM\Column(name: 'prix_menu', type: 'decimal', precision: 10, scale: 2)]
    private string $prixMenu;

    #[ORM\Column(name: 'prix_livraison', type: 'decimal', precision: 8, scale: 2)]
    private string $prixLivraison = '0.00';

    #[ORM\Column(name: 'prix_total', type: 'decimal', precision: 10, scale: 2)]
    private string $prixTotal;

    #[ORM\Column(enumType: CommandeStatut::class)]
    private CommandeStatut $statut = CommandeStatut::Nouvelle;

    #[ORM\Column(name: 'pret_materiel', type: 'boolean')]
    private bool $pretMateriel = false;

    #[ORM\Column(name: 'restitution_materiel', type: 'boolean')]
    private bool $restitutionMateriel = false;

    #[ORM\Column(name: 'motif_modification', type: 'text', nullable: true)]
    private ?string $motifModification = null;

    #[ORM\Column(name: 'mode_contact', enumType: ModeContact::class, nullable: true)]
    private ?ModeContact $modeContact = null;

    #[ORM\OneToMany(targetEntity: HistoriqueStatut::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateHeure' => 'ASC'])]
    private Collection $historiqueStatuts;

    #[ORM\OneToOne(targetEntity: Avis::class, mappedBy: 'commande')]
    private ?Avis $avis = null;

    public function __construct()
    {
        $this->historiqueStatuts = new ArrayCollection();
        $this->dateCommande      = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getNumeroCommande(): string { return $this->numeroCommande; }
    public function setNumeroCommande(string $n): static { $this->numeroCommande = $n; return $this; }
    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $u): static { $this->utilisateur = $u; return $this; }
    public function getMenu(): Menu { return $this->menu; }
    public function setMenu(Menu $menu): static { $this->menu = $menu; return $this; }
    public function getDateCommande(): \DateTimeInterface { return $this->dateCommande; }
    public function setDateCommande(\DateTimeInterface $d): static { $this->dateCommande = $d; return $this; }
    public function getDatePrestation(): \DateTimeInterface { return $this->datePrestation; }
    public function setDatePrestation(\DateTimeInterface $d): static { $this->datePrestation = $d; return $this; }
    public function getHeureLivraison(): \DateTimeInterface { return $this->heureLivraison; }
    public function setHeureLivraison(\DateTimeInterface $h): static { $this->heureLivraison = $h; return $this; }
    public function getAdresseLivraison(): string { return $this->adresseLivraison; }
    public function setAdresseLivraison(string $a): static { $this->adresseLivraison = $a; return $this; }
    public function getVilleLivraison(): string { return $this->villeLivraison; }
    public function setVilleLivraison(string $v): static { $this->villeLivraison = $v; return $this; }
    public function getNombrePersonne(): int { return $this->nombrePersonne; }
    public function setNombrePersonne(int $n): static { $this->nombrePersonne = $n; return $this; }
    public function getPrixMenu(): string { return $this->prixMenu; }
    public function setPrixMenu(string $p): static { $this->prixMenu = $p; return $this; }
    public function getPrixLivraison(): string { return $this->prixLivraison; }
    public function setPrixLivraison(string $p): static { $this->prixLivraison = $p; return $this; }
    public function getPrixTotal(): string { return $this->prixTotal; }
    public function setPrixTotal(string $p): static { $this->prixTotal = $p; return $this; }
    public function getStatut(): CommandeStatut { return $this->statut; }
    public function setStatut(CommandeStatut $statut): static { $this->statut = $statut; return $this; }
    public function isPretMateriel(): bool { return $this->pretMateriel; }
    public function setPretMateriel(bool $b): static { $this->pretMateriel = $b; return $this; }
    public function isRestitutionMateriel(): bool { return $this->restitutionMateriel; }
    public function setRestitutionMateriel(bool $b): static { $this->restitutionMateriel = $b; return $this; }
    public function getMotifModification(): ?string { return $this->motifModification; }
    public function setMotifModification(?string $m): static { $this->motifModification = $m; return $this; }
    public function getModeContact(): ?ModeContact { return $this->modeContact; }
    public function setModeContact(?ModeContact $m): static { $this->modeContact = $m; return $this; }
    public function getHistoriqueStatuts(): Collection { return $this->historiqueStatuts; }
    public function getAvis(): ?Avis { return $this->avis; }

    public function isModifiableParClient(): bool
    {
        return $this->statut->modifiableParClient();
    }
}
