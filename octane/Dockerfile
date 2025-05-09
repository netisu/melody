FROM dunglas/frankenphp:1.1-builder-php8.2.16

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

WORKDIR /var/www/html


# Start with base PHP config, then add extensions.
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/php.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the Laravel application files into the container.
COPY . .

# Install Laravel dependencies using Composer.
RUN composer install \
    --verbose \
    --no-interaction \
    --ignore-platform-reqs \
    --no-plugins \
    --no-dev \
    --prefer-dist

RUN cp -a .env.example .env

# Run only when in production
RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

# Install bun
RUN curl -fsSL https://bun.sh/install | BUN_INSTALL=/usr bash

#Build Frontend
RUN bun install
RUN bun run build
    
# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80 443

# Start Supervisor.
CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/conf.d/supervisord.conf"]