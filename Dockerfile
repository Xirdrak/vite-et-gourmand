# Image de production pour l'app Symfony "Vite & Gourmand"
# PHP 8.4 FPM + nginx dans un seul conteneur (gere par supervisor).
# Pas de Node : les assets sont geres par AssetMapper et compiles au build.
FROM php:8.4-fpm-bookworm

# Extensions PHP via l'installeur de mlocati (gere les dependances systeme)
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl opcache zip mongodb

# nginx + supervisor pour faire tourner php-fpm et nginx ensemble
RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx supervisor \
    && rm -rf /var/lib/apt/lists/*

# Composer (copie depuis l'image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Installation des dependances PHP (couche cache : copie composer.* d'abord)
COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

# Copie du code applicatif
COPY app/ ./

# Optimisation de l'autoloader + compilation du conteneur et des assets
ENV APP_ENV=prod
ENV APP_DEBUG=0
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && php bin/console cache:clear --no-debug \
    && php bin/console importmap:install \
    && php bin/console asset-map:compile \
    && chown -R www-data:www-data var

# Configuration nginx / php / supervisor
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-prod.ini /usr/local/etc/php/conf.d/zz-prod.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# L'hebergeur route le trafic HTTP vers ce port interne (PORT=8080 cote Render)
EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
