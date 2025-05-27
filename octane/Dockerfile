ARG PHP_VERSION=8.3
ARG BUN_VERSION=latest

# === STAGE 1: Composer Dependencies ===
# This stage installs Composer dependencies.
FROM composer:2 AS composer_dependencies

WORKDIR /app

# Copy only composer files to leverage Docker caching
COPY composer.json composer.lock ./

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

# === STAGE 2: Bun (Node.js/JS) Dependencies and Frontend Build ===
FROM oven/bun:${BUN_VERSION} AS bun_build

WORKDIR /app

# Copy only frontend-related files for caching
COPY package.json bun.lockb tsconfig.json vite.config.ts ./
COPY resources/js resources/css ./resources/ 

# Install Bun dependencies
RUN bun install

# Build frontend assets
RUN bun run build

# === STAGE 3: PHP Extensions and System Dependencies Build ===
FROM dunglas/frankenphp:latest-php${PHP_VERSION} AS php_extensions_build

WORKDIR /app

# Install system dependencies required for PHP extensions and runtime
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor \
    && rm -rf /var/lib/apt/lists/* # Clean up apt cache

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
    zip # Common extensions

# === STAGE 4: Final Production Image ===
# This is the leanest stage, containing only what's needed at runtime.
FROM dunglas/frankenphp:latest-php${PHP_VERSION} AS final

WORKDIR /app

# Set production environment variables
ENV SERVER_NAME="https://" 
ENV APP_ENV="production"
ENV APP_DEBUG="false"
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

# Copy PHP binary and extensions from the php_extensions_build stage
COPY --from=php_extensions_build /usr/local/bin/php /usr/local/bin/php
COPY --from=php_extensions_build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=php_extensions_build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/

# Copy system binaries needed at runtime (e.g., for supervisor, git if used by app)
COPY --from=php_extensions_build /usr/bin/supervisorctl /usr/bin/supervisorctl
COPY --from=php_extensions_build /usr/bin/supervisord /usr/bin/supervisord
COPY --from=php_extensions_build /usr/bin/git /usr/bin/git
COPY --from=php_extensions_build /usr/bin/unzip /usr/bin/unzip
COPY --from=php_extensions_build /usr/bin/procps /usr/bin/procps

# Copy compiled Composer dependencies from composer_dependencies stage
COPY --from=composer_dependencies /app/vendor /app/vendor

# Copy built frontend assets from bun_build stage
COPY --from=bun_build /app/public/build /app/public/build

# Copy application code (excluding dev-only files and already copied artifacts)
# Using a .dockerignore file is highly recommended to exclude node_modules, .git, etc.
COPY . /app

# Clean up source files that are not needed at runtime or were copied from other stages
RUN rm -rf /app/node_modules \
           /app/composer.lock \
           /app/bun.lockb \
           /app/package.json \
           /app/vite.config.js \
           /app/tsconfig.json \
           /app/resources/js \
           /app/resources/css \
           /app/.env.example # Remove example env

# Copy FrankenPHP/Octane specific configs and scripts
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/php.ini /etc/php/${PHP_VERSION}/cli/conf.d/php.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Cache Laravel application artifacts for production speed
RUN php artisan event:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan config:cache

# Regenerate Application Key
RUN php artisan key:generate

# Make entrypoint script executable
RUN chmod +x /usr/local/bin/start-container

# Set permissions for Laravel storage and cache directories
RUN chown -R frankenphp:frankenphp storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 8000

# Start Supervisor
ENTRYPOINT ["start-container"]