ARG PHP_VERSION=8.4
ARG FRANKENPHP_VERSION=1.5
ARG COMPOSER_VERSION=2.8
ARG BUN_VERSION="latest"

FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-builder-php${PHP_VERSION}

COPY --from=caddy:builder /usr/bin/xcaddy /usr/bin/xcaddy

RUN CGO_ENABLED=1 \
    XCADDY_SETCAP=1 \
    XCADDY_GO_BUILD_FLAGS="-ldflags='-w -s' -tags=nobadger,nomysql,nopgx" \
    CGO_CFLAGS=$(php-config --includes) \
    CGO_LDFLAGS="$(php-config --ldflags) $(php-config --libs)" \
    xcaddy build \
        --output /usr/local/bin/frankenphp \
        --with github.com/dunglas/frankenphp=./ \
        --with github.com/dunglas/frankenphp/caddy=./caddy/ \
        --with github.com/dunglas/caddy-cbrotli
        
WORKDIR /var/www/html
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
    
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --system --gid 1000 $WWWGROUP || true && \
    useradd --system --uid 1000 --gid $WWWGROUP --home /var/www/html --shell /bin/bash $WWWUSER || true
    
# Start with base PHP config, then add extensions.
COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/php.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
    
# Set file permissions
COPY . .

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

# Regenerate Application Key
RUN php artisan key:generate


# Install bun
RUN curl -fsSL https://bun.sh/install | BUN_INSTALL=/usr bash

#Build Frontend
RUN bun install
RUN bun run build

RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

# Start Supervisor.
ENTRYPOINT ["start-container"]
