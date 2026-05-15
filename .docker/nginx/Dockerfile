# =============================================================================
# Stage 1 – Build frontend assets
# =============================================================================
FROM node:26-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY assets/      assets/
COPY public/      public/
COPY vite.config.js ./
RUN npm run build

# =============================================================================
# Stage 2 – Production nginx (alpine, ~25 MB total)
# =============================================================================
FROM nginx:1.27-alpine

RUN rm -f /etc/nginx/conf.d/default.conf

COPY .docker/nginx/nginx.conf      /etc/nginx/nginx.conf
COPY .docker/nginx/default.conf    /etc/nginx/conf.d/default.conf

# Static files served by nginx (PHP is forwarded to php-fpm)
COPY public/                       /var/www/byte_artist/public/
COPY --from=assets /app/public/build /var/www/byte_artist/public/build

EXPOSE 80
