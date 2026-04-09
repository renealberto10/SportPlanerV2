# ── API — Development ──────────────────────────────
FROM php:8.3-cli-alpine

RUN apk add --no-cache \
        git curl zip unzip sqlite-dev libpng-dev libzip-dev \
    && docker-php-ext-install pdo pdo_sqlite zip gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

# Instala deps si no existen y levanta el servidor
CMD ["sh", "-c", \
    "composer install --no-interaction && \
     php artisan key:generate --no-interaction 2>/dev/null || true && \
     php artisan migrate --force && \
     php artisan serve --host=0.0.0.0 --port=8000"]
