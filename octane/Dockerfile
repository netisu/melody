FROM dunglas/frankenphp

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN install-php-extensions \
    cli \
    pgsql \
    gd \
    imagick \
    curl \
    imap \
    mysql \
    mbstring \
    xml \
    zip \
    bcmath \
    soap \
    intl \
    readline \
    ldap \
    msgpack \
    igbinary \
    redis \
    memcached \
    pcov \
    xdebug 

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

RUN addgroup sail \
    && adduser -h /var/www/html -s /bin/bash -G sail -D sail

COPY ./octane/start-container /usr/local/bin/start-container
COPY ./octane/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./octane/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./octane/php.ini /etc/php/8.4/cli/conf.d/99-sail

COPY . /var/www/html

RUN apt-get install memcache \
    memcached common redis mysql mysqli \
    curl bcmath


RUN composer install --ignore-platform-reqs --no-dev -a
RUN cp -a .env.example .env

# Run only when in production
RUN sed -i'' -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env

RUN php artisan key:generate

RUN bun i

RUN bun run build

RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
