# Stage 1: Build Stage
FROM dunglas/frankenphp AS build

WORKDIR /app

# Set Caddy server name to "http://" to serve on 80 and not 443
ENV SERVER_NAME="https://"

# Install build-time dependencies
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor \
    curl && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_mysql \
    redis \
    curl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --system --gid 1000 $WWWGROUP || true && \
    useradd --system --uid 1000 --gid $WWWGROUP --home /var/www/html --shell /bin/bash $WWWUSER || true

# Copy Application and Configuration
COPY . /app
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/php.ini
COPY .env .env

# Install Laravel dependencies using Composer.
RUN composer install \
    --verbose \
    --no-interaction \
    --ignore-platform-reqs \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

# Run only when in production
RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

# Cache all values
RUN php artisan event:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan config:cache && \
    php artisan optimize

# Regenerate Application Key
RUN php artisan key:generate

# Install bun and build frontend
RUN curl -fsSL https://bun.sh/install | BUN_INSTALL=/usr bash && \
    /usr/bin/bun install && \
    /usr/bin/bun run build

# Remove build-time artifacts and dev-only files
RUN rm -Rf tests/ && \
    rm -rf /app/node_modules \
    /app/composer.lock \
    /app/bun.lockb \
    /app/package.json \
    /app/vite.config.ts \
    /app/tsconfig.json \
    /app/resources/js \
    /app/resources/css \
    /app/.env.example \
    /app/resources/js/ziggy.js


# Stage 2: Production Stage
FROM dunglas/frankenphp

WORKDIR /app

# Set Caddy server name
ENV SERVER_NAME="https://"

# Copy only essential files from the build stage
COPY --from=build /etc/php/8.4/cli/conf.d/php.ini /etc/php/8.4/cli/conf.d/php.ini
COPY --from=build /app /app

# Copy runtime specific configurations and scripts
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ensure essential Laravel directories exist for runtime and set permissions
RUN mkdir -p storage bootstrap/cache bootstrap/ssr storage/framework/sessions \
    storage/framework/views storage/framework/cache storage/logs && \
    chown -R www-data:www-data storage bootstrap/cache bootstrap/ssr vendor && \
    chmod -R ug+rwx storage bootstrap/cache bootstrap/ssr vendor && \
    chmod +x /usr/local/bin/start-container

EXPOSE 8000

# Start Supervisor.
ENTRYPOINT ["start-container"]
