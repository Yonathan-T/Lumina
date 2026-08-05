# syntax=docker/dockerfile:1

##########################################################################
# Stage 1 — Build front-end assets with Vite
##########################################################################
FROM node:20-alpine AS assets

WORKDIR /app

# Install JS deps first (better layer caching)
COPY package.json package-lock.json* ./
RUN npm ci || npm install

# Build the Vite bundle. We need composer's vendor dir? No — Vite only
# needs the source. Copy the rest of the app so the manifest resolves.
COPY . .
RUN npm run build


##########################################################################
# Stage 2 — Install PHP dependencies with Composer
##########################################################################
FROM composer:2 AS vendor

WORKDIR /app

# Copy only what composer needs to resolve/install, for cache friendliness
COPY composer.json composer.lock ./

# Install without running scripts (artisan isn't fully present yet) and
# without dev dependencies for a lean production image.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --ignore-platform-req=php+


##########################################################################
# Stage 3 — Final runtime image (nginx + php-fpm + supervisor)
##########################################################################
FROM php:8.3-fpm-alpine AS runtime

# System packages + PHP extensions.
#  - postgresql-dev / pdo_pgsql : managed Postgres in production
#  - libpng/jpeg/freetype + gd  : dompdf PDF rendering
#  - icu + intl                 : localisation
#  - pcntl                      : needed by queue workers for signal handling
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        icu-libs \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_mysql \
        gd \
        intl \
        zip \
        bcmath \
        pcntl \
        opcache \
    && apk del .build-deps

WORKDIR /var/www/html

# App source
COPY . .

# Vendored PHP deps (from composer stage) and built assets (from node stage)
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Runtime config: nginx vhost, php overrides, supervisor, entrypoint
COPY docker/nginx.conf        /etc/nginx/nginx.conf
COPY docker/php.ini           /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm.conf      /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/supervisord.conf  /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh     /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Laravel needs these writable by the php-fpm/nginx user (www-data)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Render (and most PaaS) inject $PORT; nginx template reads it at boot.
ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
