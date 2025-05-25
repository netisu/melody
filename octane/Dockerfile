FROM dunglas/frankenphp

WORKDIR /app

# Set Caddy server name to "http://" to serve on 80 and not 443
# Read more: https://frankenphp.dev/docs/config/#environment-variables
ENV SERVER_NAME="https://"

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor

RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_mysql \
    redis


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --system --gid 1000 $WWWGROUP || true && \
    useradd --system --uid 1000 --gid $WWWGROUP --home /var/www/html --shell /bin/bash $WWWUSER || true

# Copy Application and Configuration
COPY . /app
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/php.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf


# Install Laravel dependencies using Composer.
RUN composer install \
    --verbose \
    --no-interaction \
    --ignore-platform-reqs \
    --no-plugins \
    --no-dev \
    --prefer-dist

# Copy application code
RUN cp -a .env.example .env

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

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

# Start Supervisor.
ENTRYPOINT ["start-container"]
