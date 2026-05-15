#!/bin/sh
set -e

WORKDIR=/var/www/byte_artist

# Ensure writable runtime directories exist (needed on fresh named volumes).
mkdir -p \
    "${WORKDIR}/var/cache" \
    "${WORKDIR}/var/log" \
    "${WORKDIR}/public/uploads" \
    "${WORKDIR}/public/images" \
    "${WORKDIR}/public/images/content/dynamisch" \
    "${WORKDIR}/public/images/upload"

# Runs as root so chown succeeds even on bind-mount directories owned by root.
# php-fpm workers drop to www-data via the pool config.
chown -R www-data:www-data \
    "${WORKDIR}/var" \
    "${WORKDIR}/public/uploads" \
    "${WORKDIR}/public/images"

exec "$@"
