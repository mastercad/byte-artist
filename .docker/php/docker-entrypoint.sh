#!/bin/sh
set -e

WORKDIR=/var/www/byte_artist

# Ensure writable runtime directories exist (needed on fresh named volumes).
mkdir -p \
    "${WORKDIR}/var/cache" \
    "${WORKDIR}/var/log" \
    "${WORKDIR}/public/uploads" \
    "${WORKDIR}/public/images/content/dynamisch" \
    "${WORKDIR}/public/images/upload"

chown -R www-data:www-data \
    "${WORKDIR}/var" \
    "${WORKDIR}/public/uploads" \
    "${WORKDIR}/public/images/content/dynamisch" \
    "${WORKDIR}/public/images/upload" \
    2>/dev/null || true

exec "$@"
