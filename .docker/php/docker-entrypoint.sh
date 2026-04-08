#!/bin/sh
set -e

WORKDIR=/var/www/byte_artist

# Ensure required directories exist.
mkdir -p \
    "${WORKDIR}/var/cache" \
    "${WORKDIR}/var/log" \
    "${WORKDIR}/public/build" \
    "${WORKDIR}/public/uploads"

# Fix ownership of directories that Docker (or a previous root run) may
# have created as root.  www-data is remapped to the host UID/GID at
# build time, so after this step the host user owns all generated files.
chown -R www-data:www-data \
    "${WORKDIR}/var" \
    "${WORKDIR}/public/build" \
    "${WORKDIR}/public/uploads"

# vendor/ may be root-owned if composer was previously run as root.
# Correct that silently so developer commands work without sudo.
if [ -d "${WORKDIR}/vendor" ]; then
    chown -R www-data:www-data "${WORKDIR}/vendor"
fi

exec "$@"
