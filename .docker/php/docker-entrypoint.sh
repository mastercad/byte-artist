#!/bin/sh
set -e

# Ensure var/ is writable by www-data (this matters when var/ is a host volume)
mkdir -p /var/www/byte_artist/var
chown -R www-data:www-data /var/www/byte_artist/var

exec "$@"
