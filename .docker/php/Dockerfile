# =============================================================================
# Stage 1 – Composer dependencies (production, no dev packages)
# =============================================================================
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# =============================================================================
# Stage 2 – Frontend assets (Vite build)
# =============================================================================
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY assets/   assets/
COPY public/   public/
COPY vite.config.js ./
RUN npm run build

# =============================================================================
# Stage 3 – Production PHP-FPM (alpine, lean)
# =============================================================================
FROM php:8.2-fpm-alpine AS production

# Runtime shared libs + fcgi for healthcheck
RUN apk add --no-cache \
        acl fcgi libzip icu-libs oniguruma libxml2 \
    && apk add --no-cache --virtual .build-deps \
        libzip-dev icu-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_mysql zip intl mbstring opcache xml \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

COPY .docker/php/php.ini          /usr/local/etc/php/conf.d/00-app.ini
COPY .docker/php/php-prod.ini     /usr/local/etc/php/conf.d/01-app-env.ini
COPY .docker/php/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/byte_artist

# Application source (vendor and public/build overridden below)
COPY --chown=www-data:www-data . .

# Inject production vendor and compiled assets
COPY --from=vendor --chown=www-data:www-data /app/vendor          ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build    ./public/build

# Ensure writable runtime directories
RUN mkdir -p var/cache var/log public/uploads \
    && chown -R www-data:www-data var public/uploads

COPY .docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER www-data
EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

# =============================================================================
# Stage 4 – Development target (adds Xdebug, Composer, Node, UID remapping)
# =============================================================================
FROM production AS development

USER root

ARG USER_ID=1000
ARG GROUP_ID=1000

# shadow provides groupmod/usermod to remap www-data → host UID so volume
# files are owned by the developer, not uid 82 (alpine default www-data).
RUN apk add --no-cache shadow git curl nodejs npm \
    && npm install -g yarn \
    && groupmod -g "${GROUP_ID}" www-data \
    && usermod  -u "${USER_ID}" www-data \
    && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .phpize-deps \
    && rm -rf /tmp/* /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Dev overrides: display_errors on, opcache revalidation enabled
COPY .docker/php/php-dev.ini   /usr/local/etc/php/conf.d/01-app-env.ini
COPY .docker/php/xdebug.ini    /usr/local/etc/php/conf.d/02-xdebug.ini

USER www-data