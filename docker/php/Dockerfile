ARG PHP_VERSION=8.2

FROM php:${PHP_VERSION}-cli

ARG UID=1000
ARG GID=1000
ARG XDEBUG_VERSION=^3.4

RUN apt-get update \
    && apt-get install -y git zip \
    && apt-get clean \
    && git config --global --add safe.directory /var/www/deptrac \
    && groupadd -g $GID deptrac && useradd --create-home -u $UID -g $GID deptrac

COPY --from=composer/composer:2-bin /composer /usr/bin/composer
COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
COPY ./docker/php/overrides.ini "$PHP_INI_DIR/conf.d/90-dev-overrides.ini"

RUN pie install xdebug/xdebug:$XDEBUG_VERSION

USER deptrac
WORKDIR /var/www/deptrac

CMD ["bash"]
