<?php

namespace App\Service;

use App\Entity\Commande;
use App\Enum\CommandeStatut;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

// Gere les statistiques admin stockees dans MongoDB.
// MySQL reste la source de verite, on copie les commandes dans Mongo pour les stats.
class StatistiquesService
{
    private const COLLECTION = 'commandes';

    public function __construct(
        private Client $client,
        private string $dbName,
    ) {}

    private function collection(): Collection
    {
        return $this->client->selectCollection($this->dbName, self::COLLECTION);
    }

    // Recopie les commandes MySQL dans MongoDB (upsert sur le numero de commande).
    public function synchroniser(array $commandes): int
    {
        if (count($commandes) === 0) {
            return 0;
        }

        $operations = [];
        foreach ($commandes as $commande) {
            /** @var Commande $commande */
            $document = [
                '_id'             => $commande->getNumeroCommande(),
                'menu_id'         => $commande->getMenu()->getId(),
                'menu_titre'      => $commande->getMenu()->getTitre(),
                'statut'          => $commande->getStatut()->value,
                'nombre_personne' => $commande->getNombrePersonne(),
                'prix_menu'       => (float) $commande->getPrixMenu(),
                'prix_livraison'  => (float) $commande->getPrixLivraison(),
                'prix_total'      => (float) $commande->getPrixTotal(),
                'date_commande'   => new UTCDateTime($commande->getDateCommande()),
                'date_prestation' => new UTCDateTime($commande->getDatePrestation()),
            ];

            $operations[] = [
                'replaceOne' => [
                    ['_id' => $commande->getNumeroCommande()],
                    $document,
                    ['upsert' => true],
                ],
            ];
        }

        $this->collection()->bulkWrite($operations);

        return count($operations);
    }

    // Nombre de commandes et CA par menu, avec filtres optionnels (periode + menu).
    // Les commandes annulees sont exclues du CA car elles ne generent pas de revenu.
    public function statistiquesParMenu(?\DateTimeInterface $debut, ?\DateTimeInterface $fin, ?int $menuId): array
    {
        $match = ['statut' => ['$ne' => CommandeStatut::Annulee->value]];

        if ($debut !== null || $fin !== null) {
            $dates = [];
            if ($debut !== null) {
                $dates['$gte'] = new UTCDateTime($debut);
            }
            if ($fin !== null) {
                $dates['$lte'] = new UTCDateTime($fin);
            }
            $match['date_commande'] = $dates;
        }

        if ($menuId !== null) {
            $match['menu_id'] = $menuId;
        }

        $pipeline = [
            ['$match' => $match],
            ['$group' => [
                '_id'        => '$menu_id',
                'menu_titre' => ['$first' => '$menu_titre'],
                'nb'         => ['$sum' => 1],
                'ca'         => ['$sum' => '$prix_total'],
            ]],
            ['$sort' => ['ca' => -1]],
        ];

        $resultats = $this->collection()->aggregate($pipeline, [
            'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
        ]);

        $lignes = [];
        foreach ($resultats as $ligne) {
            $lignes[] = [
                'menu_id'    => $ligne['_id'],
                'menu_titre' => $ligne['menu_titre'],
                'nb'         => $ligne['nb'],
                'ca'         => round((float) $ligne['ca'], 2),
            ];
        }

        return $lignes;
    }

    public function estDisponible(): bool
    {
        try {
            $this->client->selectDatabase($this->dbName)->command(['ping' => 1]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
