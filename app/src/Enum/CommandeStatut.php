<?php

namespace App\Enum;

enum CommandeStatut: string
{
    case Nouvelle                  = 'nouvelle';
    case Acceptee                  = 'acceptee';
    case EnPreparation             = 'en_preparation';
    case EnCoursLivraison          = 'en_cours_livraison';
    case Livree                    = 'livree';
    case EnAttenteRetourMateriel   = 'en_attente_retour_materiel';
    case Terminee                  = 'terminee';
    case Annulee                   = 'annulee';

    public function label(): string
    {
        return match($this) {
            self::Nouvelle                => 'Nouvelle',
            self::Acceptee                => 'Acceptée',
            self::EnPreparation           => 'En préparation',
            self::EnCoursLivraison        => 'En cours de livraison',
            self::Livree                  => 'Livrée',
            self::EnAttenteRetourMateriel => 'En attente du retour de matériel',
            self::Terminee                => 'Terminée',
            self::Annulee                 => 'Annulée',
        };
    }

    public function modifiableParClient(): bool
    {
        return $this === self::Nouvelle;
    }

    public function transitions(): array
    {
        return match($this) {
            self::Nouvelle                => [self::Acceptee],
            self::Acceptee                => [self::EnPreparation],
            self::EnPreparation           => [self::EnCoursLivraison],
            self::EnCoursLivraison        => [self::Livree],
            self::Livree                  => [self::Terminee, self::EnAttenteRetourMateriel],
            self::EnAttenteRetourMateriel => [self::Terminee],
            self::Terminee, self::Annulee => [],
        };
    }
}
