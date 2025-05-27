ARG PHP_VERSION=8.3
ARG BUN_VERSION=latest

# ---

# === STAGE 1: Composer Dependencies ===
# Installs Composer dependencies and creates initial Laravel directory structure.
FROM composer:2 AS composer_dependencies

WORKDIR /app

# Copy only Composer-related files for caching
COPY composer.json composer.lock ./

# Create essential Laravel directories needed early
RUN mkdir -p storage bootstrap/cache bootstrap/ssr storage/framework/sessions \
           storage/framework/views storage/framework/cache storage/logs

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
COPY . .
RUN composer dump-autoload -o

# === STAGE 2: PHP Asset Generation (Laravel Caching & Ziggy) ===
# Runs PHP Artisan commands that generate files for the frontend or runtime.
FROM dunglas/frankenphp:latest-php${PHP_VERSION} AS php_asset_generation

WORKDIR /app

# Copy the entire application source code
COPY . .

# Copy .env file directly into the image layer for this stage.
# It will be used by `config:cache`.
COPY .env .env

# Copy Composer vendor directory
COPY --from=composer_dependencies /app/vendor ./vendor

# Ensure vendor directory has correct permissions for Artisan commands
RUN chmod -R ug+rwx vendor

# Generate Ziggy's JavaScript routes file
RUN php artisan ziggy:generate --path=resources/js/ziggy.js

# Cache Laravel application artifacts for production speed
# These commands will now use the values from the copied .env file.
RUN php artisan event:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan config:cache

# === STAGE 3: Bun Frontend Build ===
# Installs Node.js dependencies and builds frontend assets using Bun.
FROM oven/bun:${BUN_VERSION} AS bun_build

WORKDIR /app

# Copy only frontend-related files for caching
COPY package.json bun.lockb tsconfig.json vite.config.ts ./
# Copy the entire 'resources' directory, including the generated ziggy.js
COPY --from=php_asset_generation /app/resources/ ./resources/
# Copy the vendor directory if Bun's build process somehow needs access to it
COPY --from=composer_dependencies /app/vendor ./vendor

# Install Bun dependencies (this should install `ziggy-js` if in package.json)
RUN bun install

# Build frontend assets
RUN bun run build

# === STAGES 4: Final Production Image ===
# The leanest stage, containing only what's needed at runtime.
# This stage installs PHP extensions and copies artifacts from previous stages.
FROM dunglas/frankenphp:latest-php${PHP_VERSION} AS final

WORKDIR /app

# Set production environment variables as defaults for the image
ENV SERVER_NAME="https://"
ENV APP_ENV="production"
ENV APP_DEBUG="false"
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

# Install system dependencies required for PHP extensions and runtime directly in this stage.
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions directly in this final stage.
RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_mysql \
    redis

# Copy core application files
COPY . /app

RUN rm -Rf tests/

COPY .env .env

RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

# Remove build-time artifacts and dev-only files from the final app directory
RUN rm -rf /app/node_modules \
           /app/composer.lock \
           /app/bun.lockb \
           /app/package.json \
           /app/vite.config.ts \
           /app/tsconfig.json \
           /app/resources/js \
           /app/resources/css \
           /app/.env.example \
           /app/resources/js/ziggy.js

# Copy essential runtime artifacts from previous stages
COPY --from=composer_dependencies /app/vendor ./vendor
COPY --from=bun_build /app/public/build ./public/build
COPY --from=php_asset_generation /app/bootstrap/cache/ ./bootstrap/cache/
COPY --from=php_asset_generation /app/storage/framework/views/ ./storage/framework/views/

# Copy FrankenPHP/Octane specific configs and scripts
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/php.ini /etc/php/${PHP_VERSION}/cli/conf.d/php.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ensure essential Laravel directories exist for runtime and set permissions
RUN mkdir -p storage bootstrap/cache bootstrap/ssr storage/framework/sessions \
           storage/framework/views storage/framework/cache storage/logs

# Regenerate Application Key (will use the copied .env's APP_KEY or generate if empty)
RUN php artisan key:generate

# Make entrypoint script executable
RUN chmod +x /usr/local/bin/start-container

# Set permissions for Laravel storage, cache, bootstrap, and vendor directories.
RUN chown -R www-data:www-data storage bootstrap/cache bootstrap/ssr vendor \
    && chmod -R ug+rwx storage bootstrap/cache bootstrap/ssr vendor

EXPOSE 8000

ENTRYPOINT ["start-container"]