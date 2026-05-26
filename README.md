# Vite & Gourmand

Application web pour le traiteur événementiel "Vite & Gourmand" (Bordeaux).  
Projet ECF — TP Développeur Web et Web Mobile (Studi).

---

## Prérequis

- PHP 8.2+
- Composer 2.x
- Symfony CLI 5.x
- MySQL 8.x ou 9.x
- MongoDB (pour le dashboard statistiques)

## Installation locale

```bash
# 1. Cloner le dépôt
git clone <url-du-repo> vite-et-gourmand
cd vite-et-gourmand/app

# 2. Installer les dépendances PHP
composer install

# 3. Configurer la base de données
cp .env .env.local
# Éditer .env.local : renseigner DATABASE_URL avec vos identifiants MySQL

# 4. Créer la base de données et importer le schéma
mysql -u <user> -p <nom_bdd> < ../schema.sql
mysql -u <user> -p <nom_bdd> < ../seed.sql

# 5. Lancer le serveur de développement
symfony server:start
```

L'application est accessible sur `https://localhost:8000`.

## Comptes de test

Voir `seed.sql` pour la liste complète.

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@vite-et-gourmand.fr | _voir seed.sql_ |
| Employé | employe@vite-et-gourmand.fr | _voir seed.sql_ |
| Utilisateur | client@example.com | _voir seed.sql_ |

## Structure du dépôt

```
/
├── app/          ← Application Symfony
├── schema.sql    ← Création des tables MySQL
├── seed.sql      ← Données de test
├── livrables/    ← Documents PDF du projet (charte, doc technique, etc.)
└── README.md
```

## Déploiement

Voir la documentation technique dans `livrables/doc-technique.pdf`.
