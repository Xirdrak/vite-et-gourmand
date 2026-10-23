# PHP 8.4.27 pour corriger les failles php2shell
FROM php:8.4.27-fpm-bookworm

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl opcache zip mongodb

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx supervisor \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Sinon le build plante parfois (erreurs 400 de GitHub)
ENV COMPOSER_MAX_PARALLEL_HTTP=1
COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

COPY app/ ./

ENV APP_ENV=prod
ENV APP_DEBUG=0
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && php bin/console cache:clear --no-debug \
    && php bin/console importmap:install \
    && php bin/console asset-map:compile \
    && chown -R www-data:www-data var

RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-prod.ini /usr/local/etc/php/conf.d/zz-prod.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Port attendu par Render
EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
