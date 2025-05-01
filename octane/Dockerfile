ARG WWWUSER=www-data
ARG WWWGROUP=www-data
ARG NODE_VERSION=20
ARG PHP_VERSION=8.4

FROM node:${NODE_VERSION}-alpine AS builder

WORKDIR /app

COPY package.json bun.lockb ./

RUN npm i bun -g

RUN bun install

COPY . .

RUN bun run build

# --- Get Composer ---
FROM composer:latest AS composer

WORKDIR /app

COPY composer.json composer.lock ./
COPY database/ database/
COPY . .

RUN composer install \
    -a \
    --verbose \
    --no-interaction \
    --ignore-platform-reqs \
    --no-plugins \
    --no-dev \
    --prefer-dist

    RUN composer dump-autoload --optimize

FROM ubuntu:24.04 AS production

ARG WWWUSER
ARG WWWGROUP
ARG PHP_VERSION

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y --no-install-recommends gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg

# Install PHP and essential extensions for Swoole
RUN apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && curl -sS "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c" | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends php${PHP_VERSION}-cli php${PHP_VERSION}-dev php${PHP_VERSION}-pgsql php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-gd php${PHP_VERSION}-imagick \
    php${PHP_VERSION}-curl php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath php${PHP_VERSION}-gmp php${PHP_VERSION}-intl php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-redis php${PHP_VERSION}-swoole

# Install Composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Create system user for PHP-FPM/Swoole
RUN groupadd --system --gid 1000 $WWWGROUP || true && \
    useradd --system --uid 1000 --gid $WWWGROUP --home /var/www --shell /bin/bash $WWWUSER || true

# Set file permissions
RUN chown -R $WWWUSER:$WWWGROUP .

# Copy application code
COPY --chown=$WWWUSER:$WWWGROUP . .

# Copy built frontend assets from the builder stage
COPY --from=builder /app/public/build public/build

# Copy composer vendor folder
COPY --from=composer /app/vendor vendor

# ensure .env
RUN cp -a .env.example .env
RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
# Cache all values
RUN php artisan event:cache
RUN php artisan route:cache

RUN php artisan view:cache
RUN php artisan key:generate

# Expose the port Swoole will listen on
EXPOSE 8000

# Start the Octane Swoole server
USER $WWWUSER
ENTRYPOINT ["php", "/var/www/html/artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
