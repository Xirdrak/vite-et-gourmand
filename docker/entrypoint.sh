#!/bin/sh
set -e

# Le schema et les donnees sont charges manuellement sur la base Aiven
# via schema.sql et seed.sql (pas de migration Doctrine ici).
# On s'assure juste que les dossiers de cache et de logs sont accessibles.
mkdir -p var/cache var/log
chown -R www-data:www-data var

# Dossiers d'upload des menus et plats : www-data doit pouvoir y écrire
mkdir -p public/uploads/menus public/uploads/plats
chown -R www-data:www-data public/uploads

exec "$@"
