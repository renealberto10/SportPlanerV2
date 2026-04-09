# ── API — Production ───────────────────────────────
FROM php:8.4-fpm-alpine AS base

# System deps
RUN apk add --no-cache \
        nginx supervisor \
        sqlite-dev libpng-dev libzip-dev \
        curl git zip unzip \
    && docker-php-ext-configure gd \
    && docker-php-ext-install pdo pdo_sqlite zip gd opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ── Install PHP dependencies ────────────────────────
COPY api/composer.json api/composer.lock ./
RUN composer install \
      --no-dev \
      --optimize-autoloader \
      --no-interaction \
      --no-scripts

# ── Copy application ────────────────────────────────
COPY api/ .

# Crear .env vacío (los valores reales vienen de docker-compose env)
RUN cp .env.example .env 2>/dev/null || touch .env

# ── Permissions ────────────────────────────────────
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# ── Copy configs ────────────────────────────────────
COPY docker/api/nginx.conf       /etc/nginx/http.d/default.conf
COPY docker/api/supervisord.conf /etc/supervisord.conf
COPY docker/api/php.ini          /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/api/entrypoint.sh    /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
  CMD wget -qO- http://localhost/api/v1/health 2>/dev/null || \
      wget -qO- http://localhost/ || exit 1

ENTRYPOINT ["/entrypoint.sh"]
