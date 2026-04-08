FROM php:8.2-fpm-bookworm

# Host UID/GID passed in via docker-compose build args.
# www-data is remapped to these IDs so files created inside the container
# are owned by the same user on the host — no root-owned files on volumes.
ARG USER_ID=1000
ARG GROUP_ID=1000

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl git unzip zip vim acl locales \
    libzip-dev zlib1g-dev libicu-dev libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo pdo_mysql zip intl mbstring opcache xml

# Xdebug 3.x
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Node.js 20 LTS + Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/* \
    && npm install -g yarn

# Composer 2 (via official image — no insecure flags)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/php.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-xdebug.ini
COPY php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

RUN echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen

# Remap www-data to the host UID/GID.
# If another user/group already owns these IDs, remove them first.
RUN if getent passwd ${USER_ID} > /dev/null 2>&1 \
        && [ "$(getent passwd ${USER_ID} | cut -d: -f1)" != "www-data" ]; then \
        userdel "$(getent passwd ${USER_ID} | cut -d: -f1)"; \
    fi \
    && if getent group ${GROUP_ID} > /dev/null 2>&1 \
        && [ "$(getent group ${GROUP_ID} | cut -d: -f1)" != "www-data" ]; then \
        groupdel "$(getent group ${GROUP_ID} | cut -d: -f1)"; \
    fi \
    && groupmod -g ${GROUP_ID} www-data \
    && usermod  -u ${USER_ID} -g ${GROUP_ID} www-data

WORKDIR /var/www/byte_artist/

RUN mkdir -p var && chown -R www-data:www-data var

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]