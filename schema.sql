-- ============================================================
-- Vite & Gourmand — Schéma de base de données
-- MySQL 8+ / utf8mb4
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS messenger_messages;
DROP TABLE IF EXISTS reset_password_request;
DROP TABLE IF EXISTS contact;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS historique_statut;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS plat_allergene;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS image;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS horaire;
DROP TABLE IF EXISTS allergene;
DROP TABLE IF EXISTS regime;
DROP TABLE IF EXISTS theme;
DROP TABLE IF EXISTS role;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tables de référence (pas de dépendances)
-- ------------------------------------------------------------

CREATE TABLE role (
    role_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE theme (
    theme_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE regime (
    regime_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE allergene (
    allergene_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle      VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Écart MCD : heure_ouverture/fermeture en TIME (pas VARCHAR)
CREATE TABLE horaire (
    horaire_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jour            VARCHAR(20) NOT NULL,
    heure_ouverture TIME        NOT NULL,
    heure_fermeture TIME        NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Utilisateurs
-- Écarts appliqués : nom ajouté (#1), password→VARCHAR(255) (#2),
-- actif ajouté pour désactivation des comptes employés
-- ------------------------------------------------------------

CREATE TABLE utilisateur (
    utilisateur_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id        INT UNSIGNED NOT NULL,
    email          VARCHAR(180) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    nom            VARCHAR(100) NOT NULL,
    prenom         VARCHAR(100) NOT NULL,
    telephone      VARCHAR(20)  NOT NULL,
    adresse_postale VARCHAR(255) NOT NULL,
    ville          VARCHAR(100) NOT NULL,
    pays           VARCHAR(100) NOT NULL DEFAULT 'France',
    actif          TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT uq_utilisateur_email UNIQUE (email),
    CONSTRAINT fk_utilisateur_role  FOREIGN KEY (role_id) REFERENCES role(role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_utilisateur_email  ON utilisateur (email);
CREATE INDEX idx_utilisateur_role   ON utilisateur (role_id);

-- ------------------------------------------------------------
-- Menus
-- Écarts appliqués : colonne regime supprimée→FK (#3),
-- description→TEXT (#9), conditions TEXT ajouté (#6)
-- ------------------------------------------------------------

CREATE TABLE menu (
    menu_id                 INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    theme_id                INT UNSIGNED   NOT NULL,
    regime_id               INT UNSIGNED   NOT NULL,
    titre                   VARCHAR(150)   NOT NULL,
    description             TEXT           NOT NULL,
    nombre_personne_minimum INT UNSIGNED   NOT NULL,
    prix_par_personne       DECIMAL(8,2)   NOT NULL,
    quantite_restante       INT UNSIGNED   NOT NULL DEFAULT 0,
    conditions              TEXT,
    actif                   TINYINT(1)     NOT NULL DEFAULT 1,
    CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme(theme_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_menu_theme_regime ON menu (theme_id, regime_id);
CREATE INDEX idx_menu_actif        ON menu (actif);

-- Écart #4 : galerie d'images absente du MCD → table image
CREATE TABLE image (
    image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id  INT UNSIGNED  NOT NULL,
    chemin   VARCHAR(255)  NOT NULL,
    ordre    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_image_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_image_menu ON image (menu_id);

-- ------------------------------------------------------------
-- Plats
-- Écarts appliqués : photo BLOB→VARCHAR chemin (#4 partiel),
-- type_plat ENUM ajouté (#12)
-- ------------------------------------------------------------

CREATE TABLE plat (
    plat_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre_plat VARCHAR(150) NOT NULL,
    type_plat  ENUM('entree', 'plat', 'dessert') NOT NULL,
    photo      VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Liaisons many-to-many

CREATE TABLE menu_plat (
    menu_id INT UNSIGNED NOT NULL,
    plat_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    CONSTRAINT fk_menu_plat_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_menu_plat_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plat_allergene (
    plat_id      INT UNSIGNED NOT NULL,
    allergene_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    CONSTRAINT fk_plat_allergene_plat      FOREIGN KEY (plat_id)      REFERENCES plat(plat_id)      ON DELETE CASCADE,
    CONSTRAINT fk_plat_allergene_allergene FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Commandes
-- Écarts appliqués : commande_id PK AUTO_INCREMENT + numero_commande UNIQUE (#7),
-- motif_modification + mode_contact ajoutés (#8),
-- adresse/ville livraison (exigence formulaire commande)
-- ------------------------------------------------------------

CREATE TABLE commande (
    commande_id       INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    numero_commande   VARCHAR(20)    NOT NULL,
    utilisateur_id    INT UNSIGNED   NOT NULL,
    menu_id           INT UNSIGNED   NOT NULL,
    date_commande     DATETIME       NOT NULL,
    date_prestation   DATE           NOT NULL,
    heure_livraison   TIME           NOT NULL,
    adresse_livraison VARCHAR(255)   NOT NULL,
    ville_livraison   VARCHAR(100)   NOT NULL,
    nombre_personne   INT UNSIGNED   NOT NULL,
    prix_menu         DECIMAL(10,2)  NOT NULL,
    prix_livraison    DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
    prix_total        DECIMAL(10,2)  NOT NULL,
    statut            ENUM(
                          'nouvelle',
                          'acceptee',
                          'en_preparation',
                          'en_cours_livraison',
                          'livree',
                          'en_attente_retour_materiel',
                          'terminee',
                          'annulee'
                      ) NOT NULL DEFAULT 'nouvelle',
    pret_materiel          TINYINT(1) NOT NULL DEFAULT 0,
    restitution_materiel   TINYINT(1) NOT NULL DEFAULT 0,
    motif_modification     TEXT,
    mode_contact           ENUM('gsm', 'email'),
    CONSTRAINT uq_commande_numero    UNIQUE (numero_commande),
    CONSTRAINT fk_commande_client    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    CONSTRAINT fk_commande_menu      FOREIGN KEY (menu_id)        REFERENCES menu(menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_commande_client ON commande (utilisateur_id);
CREATE INDEX idx_commande_statut ON commande (statut);
CREATE INDEX idx_commande_date   ON commande (date_prestation);

-- Écart #5 : suivi de commande avec horodatage de chaque changement de statut
CREATE TABLE historique_statut (
    historique_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id   INT UNSIGNED NOT NULL,
    statut        VARCHAR(50)  NOT NULL,
    date_heure    DATETIME     NOT NULL,
    commentaire   TEXT,
    CONSTRAINT fk_historique_commande FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_historique_commande ON historique_statut (commande_id);

-- ------------------------------------------------------------
-- Avis
-- Écarts appliqués : commande_id FK ajoutée (#10),
-- note CHECK 1-5, statut ENUM, date_avis ajoutée
-- ------------------------------------------------------------

CREATE TABLE avis (
    avis_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    commande_id    INT UNSIGNED NOT NULL,
    note           TINYINT UNSIGNED NOT NULL,
    description    TEXT         NOT NULL,
    statut         ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente',
    date_avis      DATETIME     NOT NULL,
    CONSTRAINT chk_avis_note         CHECK (note BETWEEN 1 AND 5),
    CONSTRAINT uq_avis_commande       UNIQUE (commande_id),
    CONSTRAINT fk_avis_utilisateur   FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    CONSTRAINT fk_avis_commande      FOREIGN KEY (commande_id)    REFERENCES commande(commande_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_avis_statut ON avis (statut);

-- Écart #11 : table contact pour traçabilité des demandes (formulaire → BDD + mail)
CREATE TABLE contact (
    contact_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(180) NOT NULL,
    titre       VARCHAR(200) NOT NULL,
    description TEXT         NOT NULL,
    date_envoi  DATETIME     NOT NULL,
    traite      TINYINT(1)   NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour la réinitialisation de mot de passe
CREATE TABLE reset_password_request (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    selector      VARCHAR(20)  NOT NULL,
    hashed_token  VARCHAR(100) NOT NULL,
    requested_at  DATETIME     NOT NULL,
    expires_at    DATETIME     NOT NULL,
    CONSTRAINT fk_reset_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table infrastructure Symfony Messenger (transport doctrine)
CREATE TABLE messenger_messages (
    id           BIGINT AUTO_INCREMENT PRIMARY KEY,
    body         LONGTEXT    NOT NULL,
    headers      LONGTEXT    NOT NULL,
    queue_name   VARCHAR(190) NOT NULL,
    created_at   DATETIME    NOT NULL,
    available_at DATETIME    NOT NULL,
    delivered_at DATETIME    DEFAULT NULL,
    INDEX idx_messenger_queue (queue_name, available_at, delivered_at, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
