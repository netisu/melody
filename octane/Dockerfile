ARG PHP_VERSION=8.3
ARG BUN_VERSION=latest

# === STAGE 1: Composer Dependencies ===
# This stage installs Composer dependencies.
FROM composer:2 AS composer_dependencies

WORKDIR /app

# Copy only composer files to leverage Docker caching
COPY composer.json composer.lock ./
RUN mkdir storage storage/framework storage/logs \
    && cd storage/framework && mkdir sessions views cache && cd ../.. \
    && chown -R www-data:www-data storage
# Install Composer dependencies for production
RUN composer install \
    --verbose \
    --no-interaction \
    --ignore-platform-reqs \
    --prefer-dist \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --optimize-autoloader

# === STAGE 1.5: Generate Laravel Application Assets (including Ziggy) ===
FROM dunglas/frankenphp:latest-php${PHP_VERSION} AS php_asset_generation

WORKDIR /app
COPY . .
COPY --from=composer_dependencies /app/vendor ./vendor

RUN php artisan ziggy:generate --path=resources/js/ziggy.js

RUN php artisan event:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan config:cache

# === STAGE 2: Bun (Node.js/JS) Dependencies and Frontend Build ===
FROM oven/bun:${BUN_VERSION} AS bun_build

WORKDIR /app

# Copy only frontend-related files for caching
COPY package.json bun.lockb tsconfig.json vite.config.ts ./
COPY --from=composer_dependencies /app/vendor ./vendor
COPY --from=php_asset_generation /app/resources/ ./resources/

# Install Bun dependencies
RUN bun install

# Build frontend assets
RUN bun run build

# === STAGE 3: PHP Extensions and System Dependencies Build ===
FROM dunglas/frankenphp:latest-php${PHP_VERSION} AS php_extensions_build

WORKDIR /app

ENV SERVER_NAME="https://"
ENV APP_ENV="production"
ENV APP_DEBUG="false"
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

# Install system dependencies required for PHP extensions and runtime
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions using FrankenPHP's helper and pecl
RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_mysql \
    redis \
    intl \
    exif \
    sockets \
    zip
COPY --from=composer_dependencies /app/vendor /app/vendor
COPY --from=bun_build /app/public/build /app/public/build

COPY . /app

# Remove build-time artifacts and dev-only files to keep the final image lean
RUN rm -rf /app/node_modules \
    /app/composer.lock \
    /app/bun.lockb \
    /app/package.json \
    /app/vite.config.js \
    /app/tsconfig.json \
    /app/resources/js \
    /app/resources/css \
    /app/.env.example \
    /app/resources/js/ziggy.js

# Copy FrankenPHP/Octane specific configs and scripts
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/php.ini /etc/php/${PHP_VERSION}/cli/conf.d/php.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# Copy Laravel cached config/routes/views from php_asset_generation stage
COPY --from=php_asset_generation /app/bootstrap/cache/ ./bootstrap/cache/
COPY --from=php_asset_generation /app/storage/framework/views/ ./storage/framework/views/

# Regenerate Application Key
RUN php artisan key:generate

# Make entrypoint script executable
RUN chmod +x /usr/local/bin/start-container


# Set permissions for Laravel storage, cache, and vendor directories.
RUN chown -R frankenphp:frankenphp storage bootstrap/cache vendor \
    && chmod -R ug+rwx storage bootstrap/cache vendor

EXPOSE 8000

ENTRYPOINT ["start-container"]