FROM dunglas/frankenphp

WORKDIR /app

# Set Caddy server name to "http://" to serve on 80 and not 443
# Read more: https://frankenphp.dev/docs/config/#environment-variables
ENV SERVER_NAME="https://"

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    zip \
    unzip \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    libcap2-bin \
    libpng-dev \
    dnsutils \
    librsvg2-bin \
    fswatch \
    ffmpeg \
    gnupg \
    gosu \
    supervisor \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions
RUN install-php-extensions \
    gd \
    imagick \
    curl \
    pgsql  \
    mysqli  \
    mbstring \
    xml \
    zip \
    bcmath \
    soap \
    intl \
    readline \
    ldap \
    igbinary \
    msgpack \
    pcov \
    redis \
    pcntl \
    pdo_mysql \
    imap \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --system --gid 1000 $WWWGROUP || true && \
    useradd --system --uid 1000 --gid $WWWGROUP --home /var/www/html --shell /bin/bash $WWWUSER || true

# Copy Application and Configuration
COPY . /app
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/99-octane.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
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
RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan config:cache
RUN php artisan optimize

# Regenerate Application Key
RUN php artisan key:generate


# Install bun
RUN curl -fsSL https://bun.sh/install | BUN_INSTALL=/usr bash

#Build Frontend
RUN bun install
RUN bun run build

RUN chmod +x /usr/local/bin/start-container

# Remove build-time artifacts and dev-only files from the final app directory
RUN rm -Rf tests/

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

# Ensure essential Laravel directories exist for runtime and set permissions
RUN mkdir -p storage bootstrap/cache bootstrap/ssr storage/framework/sessions \
           storage/framework/views storage/framework/cache storage/logs

# Set permissions for Laravel storage, cache, bootstrap, and vendor directories.
RUN chown -R www-data:www-data storage bootstrap/cache bootstrap/ssr vendor \
    && chmod -R ug+rwx storage bootstrap/cache bootstrap/ssr vendor

EXPOSE 8000

# Start Supervisor.
ENTRYPOINT ["start-container"]
