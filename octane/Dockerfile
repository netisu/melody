FROM ubuntu:24.04

WORKDIR /var/www/html

ENV WWWUSER=sail \
    WWWGROUP=sail
ENV NODE_VERSION=20

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.4-cli php8.4-dev \
    php8.4-pgsql php8.4-sqlite3 php8.4-gd php8.4-imagick \
    php8.4-curl \
    php8.4-imap php8.4-mysql php8.4-mbstring \
    php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap \
    php8.4-intl php8.4-readline \
    php8.4-ldap \
    php8.4-msgpack php8.4-igbinary php8.4-redis php8.4-swoole \
    php8.4-memcached php8.4-pcov php8.4-xdebug \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && npm install -g bun \
    && apt-get update \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

RUN addgroup sail \
    && adduser -h /var/www/html -s /bin/bash -G sail -D sail

COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/99-sail

COPY . .

RUN apt-get install php8.4-memcache \
    php8.4-memcached php8.4-common php8.4-redis php8.4-mysql php8.4-mysqli \
    php8.4-curl php8.4-bcmath


RUN composer install --ignore-platform-req=ext-gmp --no-scripts
RUN cp -a .env.example .env

# Run only when in production
RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

RUN php artisan key:generate

RUN bun i

RUN bun run build

RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
