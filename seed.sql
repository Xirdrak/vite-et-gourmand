-- Vite & Gourmand - donnees de test
-- Mots de passe : Admin@2026! / Employe@2026! / Client@2026!

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE reset_password_request;
TRUNCATE TABLE contact;
TRUNCATE TABLE avis;
TRUNCATE TABLE historique_statut;
TRUNCATE TABLE commande;
TRUNCATE TABLE plat_allergene;
TRUNCATE TABLE menu_plat;
TRUNCATE TABLE plat;
TRUNCATE TABLE image;
TRUNCATE TABLE menu;
TRUNCATE TABLE utilisateur;
TRUNCATE TABLE horaire;
TRUNCATE TABLE allergene;
TRUNCATE TABLE regime;
TRUNCATE TABLE theme;
TRUNCATE TABLE role;

SET FOREIGN_KEY_CHECKS = 1;

-- Rôles
INSERT INTO role (role_id, libelle) VALUES
(1, 'utilisateur'),
(2, 'employe'),
(3, 'administrateur');

-- Thèmes
INSERT INTO theme (theme_id, libelle) VALUES
(1, 'Noël'),
(2, 'Pâques'),
(3, 'Classique'),
(4, 'Événement');

-- Régimes
INSERT INTO regime (regime_id, libelle) VALUES
(1, 'Classique'),
(2, 'Végétarien'),
(3, 'Vegan');

-- 14 allergènes réglementaires EU
INSERT INTO allergene (allergene_id, libelle) VALUES
(1,  'Gluten'),
(2,  'Crustacés'),
(3,  'Œufs'),
(4,  'Poisson'),
(5,  'Arachides'),
(6,  'Soja'),
(7,  'Lait'),
(8,  'Fruits à coque'),
(9,  'Céleri'),
(10, 'Moutarde'),
(11, 'Sésame'),
(12, 'Sulfites'),
(13, 'Lupin'),
(14, 'Mollusques');

-- Horaires lundi → dimanche
INSERT INTO horaire (horaire_id, jour, heure_ouverture, heure_fermeture) VALUES
(1, 'Lundi',    '09:00:00', '19:00:00'),
(2, 'Mardi',    '09:00:00', '19:00:00'),
(3, 'Mercredi', '09:00:00', '19:00:00'),
(4, 'Jeudi',    '09:00:00', '19:00:00'),
(5, 'Vendredi', '09:00:00', '19:00:00'),
(6, 'Samedi',   '09:00:00', '17:00:00'),
(7, 'Dimanche', '10:00:00', '14:00:00');

-- Utilisateurs (admin créé en BDD uniquement, impossible via l'application)
INSERT INTO utilisateur (utilisateur_id, role_id, email, password, nom, prenom, telephone, adresse_postale, ville, pays, actif) VALUES
(1, 3, 'admin@vite-et-gourmand.fr',       '$2y$12$O/aznvIk3XhS5ZbGBJxs3uvZgYFL.TFEuUG8dKRpoMxyVF9c063wi', 'Moreau', 'Julie',  '0556001122', '12 rue du Traiteur',   'Bordeaux', 'France', 1),
(2, 2, 'marie.dupont@vite-et-gourmand.fr', '$2y$12$XJymbUguTJYV0qSRyRFEqexm/J3M/H6zyEwBOJOEPuLbx8Hk0/LpG', 'Dupont', 'Marie',  '0556334455', '3 allée des Pins',     'Bordeaux', 'France', 1),
(3, 2, 'thomas.leroy@vite-et-gourmand.fr', '$2y$12$XJymbUguTJYV0qSRyRFEqexm/J3M/H6zyEwBOJOEPuLbx8Hk0/LpG', 'Leroy',  'Thomas', '0556778899', '8 cours de la Marne',  'Bordeaux', 'France', 1),
(4, 1, 'alice.martin@example.com',         '$2y$12$DkCDYLHi3EEka9gnfPVnYuIHTCdSVx4ZKmAJJ/NdJXmgsn6eopMTm', 'Martin', 'Alice',  '0612345678', '25 avenue de la Paix', 'Bordeaux', 'France', 1),
(5, 1, 'bob.durand@example.com',           '$2y$12$DkCDYLHi3EEka9gnfPVnYuIHTCdSVx4ZKmAJJ/NdJXmgsn6eopMTm', 'Durand', 'Bob',    '0698765432', '7 rue des Vignes',     'Mérignac', 'France', 1),
(6, 1, 'claire.petit@example.com',         '$2y$12$DkCDYLHi3EEka9gnfPVnYuIHTCdSVx4ZKmAJJ/NdJXmgsn6eopMTm', 'Petit',  'Claire', '0677889900', '14 impasse des Roses', 'Pessac',   'France', 1);

-- Menus
INSERT INTO menu (menu_id, theme_id, regime_id, titre, description, nombre_personne_minimum, prix_par_personne, quantite_restante, conditions, actif) VALUES
(1, 1, 1, 'Le Grand Noël',
 'Un menu de fête raffiné pour célébrer Noël en famille ou entre collègues. Produits régionaux du Sud-Ouest sélectionnés avec soin.',
 10, 45.00, 8,
 'Commande au minimum 7 jours avant la prestation. Conserver au réfrigérateur entre 0 et 4°C. Consommer dans les 48h après livraison.',
 1),
(2, 2, 2, 'Printemps Végétarien',
 'Un menu de Pâques tout en légèreté, 100% végétarien, qui célèbre les saveurs du printemps avec des légumes de saison.',
 8, 38.00, 12,
 'Commande au minimum 5 jours avant la prestation. Sans viande ni poisson. Contient des produits laitiers et des œufs.',
 1),
(3, 3, 3, 'L''Essentiel Vegan',
 'Notre menu classique entièrement vegan, sans compromis sur le goût. Idéal pour les événements mixtes avec des convives aux régimes variés.',
 6, 35.00, 15,
 'Commande au minimum 5 jours avant la prestation. Aucun produit d''origine animale. Convient aux personnes intolérantes au lactose.',
 1),
(4, 4, 1, 'Excellence Événement',
 'Notre menu prestige pour les grandes occasions : mariages, séminaires, soirées de gala. Produits d''exception, service impeccable.',
 20, 55.00, 4,
 'Commande au minimum 14 jours avant la prestation. Livraison avec équipement de maintien en température. Caution matériel : 600€.',
 1);

-- Images des menus
INSERT INTO image (menu_id, chemin, ordre) VALUES
(1, 'images/menus/noel-entree.webp',     0),
(1, 'images/menus/noel-plat.webp',       1),
(1, 'images/menus/noel-dessert.webp',    2),
(2, 'images/menus/paques-entree.webp',   0),
(2, 'images/menus/paques-plat.webp',     1),
(3, 'images/menus/vegan-plat.webp',      0),
(4, 'images/menus/evenement-table.webp', 0),
(4, 'images/menus/evenement-plat.webp',  1);

-- Plats
INSERT INTO plat (plat_id, titre_plat, type_plat, photo) VALUES
(1,  'Velouté de potiron au foie gras',    'entree',  'images/plats/veloute-potiron.webp'),
(2,  'Chapon rôti aux marrons',            'plat',    'images/plats/chapon-marrons.webp'),
(3,  'Bûche de Noël aux agrumes',          'dessert', 'images/plats/buche-noel.webp'),
(4,  'Salade d''asperges et burrata',      'entree',  'images/plats/asperges-burrata.webp'),
(5,  'Risotto aux champignons et parmesan','plat',    'images/plats/risotto-champignons.webp'),
(6,  'Tarte au citron meringuée',          'dessert', 'images/plats/tarte-citron.webp'),
(7,  'Gaspacho de tomates et basilic',     'entree',  'images/plats/gaspacho-tomates.webp'),
(8,  'Tajine de légumes aux épices douces','plat',    'images/plats/tajine-legumes.webp'),
(9,  'Fondant au chocolat noir',           'dessert', 'images/plats/fondant-chocolat.webp'),
(10, 'Foie gras mi-cuit au torchon',       'entree',  'images/plats/foie-gras.webp'),
(11, 'Filet de bœuf Wellington',           'plat',    'images/plats/boeuf-wellington.webp'),
(12, 'Soufflé au Grand Marnier',           'dessert', 'images/plats/souffle-grand-marnier.webp'),
(13, 'Sorbet mangue et fruits de la passion', 'dessert', 'images/plats/sorbet-mangue.webp');

INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1),(1, 2),(1, 3),
(2, 4),(2, 5),(2, 6),
(3, 7),(3, 8),(3,13),
(4,10),(4,11),(4,12),(4, 9);

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(1,  7),(1, 12),
(2,  1),
(3,  1),(3, 3),(3, 7),
(4,  7),(4, 3),
(5,  7),
(6,  1),(6, 3),(6, 7),
(7, 12),
(9,  1),(9, 7),(9, 3),
(10,12),
(11, 1),(11, 3),
(12, 7),(12, 3);

-- Commandes (une par statut du workflow)
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison, adresse_livraison, ville_livraison, nombre_personne, prix_menu, prix_livraison, prix_total, statut, pret_materiel, restitution_materiel) VALUES
(1, 'VG-2026-00001', 4, 3, '2026-05-25 14:30:00', '2026-06-15', '12:00:00', '25 avenue de la Paix',  'Bordeaux',  8,  280.00,    0.00,    280.00, 'nouvelle',                   0, 0),
(2, 'VG-2026-00002', 5, 1, '2026-05-20 10:00:00', '2026-07-04', '13:00:00', '15 rue des Pins',       'Mérignac', 15,  607.50,   12.08,    619.58, 'acceptee',                   0, 0),
(3, 'VG-2026-00003', 4, 2, '2026-05-15 16:00:00', '2026-06-20', '11:30:00', '8 chemin des Collines', 'Mérignac', 10,  380.00,   19.75,    399.75, 'en_preparation',             0, 0),
(4, 'VG-2026-00004', 6, 4, '2026-05-10 11:30:00', '2026-05-26', '12:00:00', '1 allée du Château',   'Pessac',   25, 1237.50,   13.85,   1251.35, 'en_cours_livraison',         1, 0),
(5, 'VG-2026-00005', 5, 3, '2026-04-10 09:00:00', '2026-04-20', '12:00:00', '7 rue des Vignes',     'Mérignac',  6,  210.00,   18.57,    228.57, 'terminee',                   0, 0),
(6, 'VG-2026-00006', 4, 4, '2026-04-15 10:00:00', '2026-05-05', '11:00:00', '25 avenue de la Paix', 'Bordeaux', 30, 1485.00,    0.00,   1485.00, 'en_attente_retour_materiel', 1, 0),
(7, 'VG-2026-00007', 6, 1, '2026-03-01 14:00:00', '2026-03-15', '12:00:00', '14 impasse des Roses', 'Pessac',   12,  540.00,   22.70,    562.70, 'terminee',                   0, 0);

-- Historique des statuts
INSERT INTO historique_statut (commande_id, statut, date_heure) VALUES
(1, 'nouvelle',                   '2026-05-25 14:30:00'),
(2, 'nouvelle',                   '2026-05-20 10:00:00'),
(2, 'acceptee',                   '2026-05-21 09:15:00'),
(3, 'nouvelle',                   '2026-05-15 16:00:00'),
(3, 'acceptee',                   '2026-05-16 10:00:00'),
(3, 'en_preparation',             '2026-05-18 08:30:00'),
(4, 'nouvelle',                   '2026-05-10 11:30:00'),
(4, 'acceptee',                   '2026-05-11 14:00:00'),
(4, 'en_preparation',             '2026-05-20 07:00:00'),
(4, 'en_cours_livraison',         '2026-05-26 06:30:00'),
(5, 'nouvelle',                   '2026-04-10 09:00:00'),
(5, 'acceptee',                   '2026-04-11 10:00:00'),
(5, 'en_preparation',             '2026-04-18 08:00:00'),
(5, 'en_cours_livraison',         '2026-04-20 10:30:00'),
(5, 'livree',                     '2026-04-20 12:45:00'),
(5, 'terminee',                   '2026-04-20 12:46:00'),
(6, 'nouvelle',                   '2026-04-15 10:00:00'),
(6, 'acceptee',                   '2026-04-16 09:00:00'),
(6, 'en_preparation',             '2026-04-28 07:00:00'),
(6, 'en_cours_livraison',         '2026-05-05 09:30:00'),
(6, 'livree',                     '2026-05-05 13:00:00'),
(6, 'en_attente_retour_materiel', '2026-05-05 13:05:00'),
(7, 'nouvelle',                   '2026-03-01 14:00:00'),
(7, 'acceptee',                   '2026-03-02 09:00:00'),
(7, 'en_preparation',             '2026-03-10 07:00:00'),
(7, 'en_cours_livraison',         '2026-03-15 09:00:00'),
(7, 'livree',                     '2026-03-15 12:15:00'),
(7, 'terminee',                   '2026-03-15 12:16:00');

-- Avis sur commandes terminées
INSERT INTO avis (avis_id, utilisateur_id, commande_id, note, description, statut, date_avis) VALUES
(1, 5, 5, 5, 'Service impeccable, plats délicieux et livrés à l''heure. Le chapon était fondant à souhait. Je recommande vivement !', 'valide',     '2026-04-21 10:15:00'),
(2, 6, 7, 4, 'Très bon repas dans l''ensemble, le velouté de potiron était excellent. Légère déception sur la bûche un peu sèche.',   'en_attente', '2026-03-16 09:30:00');

-- Exemple de demande de contact
INSERT INTO contact (email, titre, description, date_envoi, traite) VALUES
('prospect@example.com', 'Demande de devis pour mariage', 'Bonjour, je souhaite organiser un mariage en septembre 2026 pour environ 80 personnes. Pouvez-vous me proposer une formule adaptée ?', '2026-05-24 11:00:00', 0);
