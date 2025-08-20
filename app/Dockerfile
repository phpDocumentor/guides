FROM php:8.4-cli

COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie

RUN apt update && apt install -y git

RUN docker-php-ext-configure pcntl --enable-pcntl \
  && docker-php-ext-install pcntl \
  && pie install arnaud-lb/inotify

