# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Base image: PHP 8.3 + Apache with the extensions the app needs
# ---------------------------------------------------------------------------
FROM php:8.3-apache AS base

COPY --from=mlocati/php-extension-installer:2.7.0 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/* \
 && install-php-extensions pdo_mysql intl opcache zip

# Serve the Symfony front controller from /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

COPY docker/php/app.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
# Strip Windows line endings in case the repo was checked out with CRLF
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint && chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /var/www/html
ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]

# ---------------------------------------------------------------------------
# Development image: dev dependencies, debug toolbar, live-mounted sources
# ---------------------------------------------------------------------------
FROM base AS dev

ENV APP_ENV=dev
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY composer.json composer.lock symfony.lock ./
RUN composer install --prefer-dist --no-scripts --no-autoloader --no-progress

COPY . .
RUN composer dump-autoload \
 && composer run-script post-install-cmd \
 && mkdir -p var && chown -R www-data:www-data var

# ---------------------------------------------------------------------------
# Production image: no dev dependencies, compiled assets, optimised autoloader
# ---------------------------------------------------------------------------
FROM base AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader --no-progress

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative \
 && composer run-script --no-dev post-install-cmd \
 && php bin/console asset-map:compile \
 && mkdir -p var && chown -R www-data:www-data var
