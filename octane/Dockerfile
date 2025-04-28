ARG ${WWWUSER}=www-data
ARG ${WWWGROUP}=www-data
ARG NODE_VERSION=23
ARG PHP_VERSION=8.4

FROM ubuntu:24.04 AS base

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y --no-install-recommends gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg

# Install PHP and essential extensions
RUN apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && curl -sS "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c" | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends php${PHP_VERSION}-cli php${PHP_VERSION}-fpm php${PHP_VERSION}-pgsql php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-gd php${PHP_VERSION}-imagick \
    php${PHP_VERSION}-curl php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath php${PHP_VERSION}-intl php${PHP_VERSION}-mysql

# Install Composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Install Node.js and npm (use npm to globally install bun and build the frontend assets)
RUN curl -sSL "https://deb.nodesource.com/setup_${NODE_VERSION}.x" | bash - 

RUN apt-get update \
    && apt-get install -y --no-install-recommends nodejs

RUN apt-get update \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Create system user for PHP-FPM/FrankenPHP
RUN addgroup --system ${WWWGROUP} && \
    adduser --system --gid ${WWWGROUP} --home /var/www --shell /bin/bash ${WWWUSER}

# Set file permissions
RUN chown -R ${WWWUSER}:${WWWGROUP} /var/www/html

# Copy application code
COPY --chown=${WWWUSER}:${WWWGROUP} . /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan config:cache
RUN php artisan view:cache
RUN php artisan migrate --force
RUN php artisan queue:restart

# Run frontend builds in production
RUN bun install --frozen-lockfile --production
RUN bun run build

# --- FrankenPHP Stage ---
FROM base AS frankenphp

ARG ${WWWUSER}
ARG ${WWWGROUP}

# Install FrankenPHP
RUN curl -fsSL https://get.frankenphp.dev/install.sh | bash

# Create necessary directories
RUN mkdir -p /etc/frankenphp/certs

# Copy application (again, to ensure correct user in final image)
COPY --chown=${WWWUSER}:${WWWGROUP} . /var/www/html

# Copy PHP configuration files
COPY ./octane/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./octane/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set permissions for storage and bootstrap cache
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 443

# Run FrankenPHP
ENTRYPOINT ["/usr/local/bin/frankenphp", "run", "--config=/etc/frankenphp/config.toml"]