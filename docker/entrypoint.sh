#!/bin/sh
set -e

# Pas de migration ici, la base est créée avec schema.sql et seed.sql
mkdir -p var/cache var/log
chown -R www-data:www-data var

mkdir -p public/uploads/menus public/uploads/plats
chown -R www-data:www-data public/uploads

exec "$@"
