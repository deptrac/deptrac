ARG PHP_VERSION=8.1

FROM php:${PHP_VERSION}-cli

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y git zip \
    && pecl install xdebug \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && pecl clear-cache

RUN git config --global --add safe.directory /var/www/deptrac
RUN groupadd -g $GID deptrac && useradd --create-home -u $UID -g $GID deptrac

COPY --from=composer/composer:2-bin /composer /usr/bin/composer
COPY ./docker/php/overrides.ini "$PHP_INI_DIR/conf.d/90-dev-overrides.ini"

USER deptrac
WORKDIR /var/www/deptrac

CMD ["bash"]
