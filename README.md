# Vite & Gourmand

Application web pour le traiteur événementiel "Vite & Gourmand" (Bordeaux).  
Projet ECF - TP Développeur Web et Web Mobile (Studi).

Application en ligne : https://vite-et-gourmand-bxiy.onrender.com

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

Comptes créés par `seed.sql` (les mots de passe sont communs par rôle).

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@vite-et-gourmand.fr | Admin@2026! |
| Employé | marie.dupont@vite-et-gourmand.fr | Employe@2026! |
| Employé | thomas.leroy@vite-et-gourmand.fr | Employe@2026! |
| Utilisateur | alice.martin@example.com | Client@2026! |
| Utilisateur | bob.durand@example.com | Client@2026! |
| Utilisateur | claire.petit@example.com | Client@2026! |

Le compte administrateur est inséré directement en base via `seed.sql` : il n'est pas créable depuis l'application (exigence du sujet).

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

L'application est déployée sur Render à partir d'un conteneur Docker, avec les
bases de données hébergées séparément :

- Application : Render (web service Docker, build du `Dockerfile` à la racine)
- Base MySQL : Aiven (service managé, connexion SSL via le certificat CA dans `app/config/certs/`)
- Base MongoDB : MongoDB Atlas (cluster M0, pour le dashboard statistiques)
- Envoi des mails : Mailtrap (SMTP de test)

### Principe

Le fichier `render.yaml` à la racine décrit le service (runtime Docker, port,
health check). À chaque push sur la branche suivie, Render reconstruit l'image à
partir du `Dockerfile` (PHP 8.4-fpm + nginx pilotés par supervisor) et redéploie.

Les secrets de production ne sont pas dans le dépôt : ils sont déclarés
`sync: false` dans `render.yaml` et leurs valeurs sont saisies dans le dashboard
Render (variables d'environnement) :

- `APP_SECRET`
- `DATABASE_URL` (base Aiven)
- `MONGODB_URI`, `MONGODB_DB` (cluster Atlas)
- `MAILER_DSN` (Mailtrap)
- `DEFAULT_URI` (URL publique de l'application)

### Préparation des bases

Le schéma et les données sont chargés une fois sur la base Aiven, comme en local :

```bash
mysql --host=<host-aiven> --port=<port-aiven> --user=avnadmin -p --ssl-mode=REQUIRED defaultdb < schema.sql
mysql --host=<host-aiven> --port=<port-aiven> --user=avnadmin -p --ssl-mode=REQUIRED defaultdb < seed.sql
```

La démarche complète figure aussi dans la documentation technique
(`livrables/doc-technique.pdf`).
