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
}
