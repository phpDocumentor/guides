FROM php:8.4-cli AS builder

COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie

RUN export DEBIAN_FRONTEND="noninteractive"; \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        unzip \
        curl \
        git \
        ca-certificates \
        build-essential \
        pkg-config \
        libssl-dev \
        libclang-dev \
        llvm-dev; \
    rm -rf /var/lib/apt/lists/*

ENV RUSTUP_HOME=/usr/local/rustup \
    CARGO_HOME=/usr/local/cargo \
    PATH=/usr/local/cargo/bin:$PATH \
    RUST_VERSION=1.82.0

RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | \
    sh -s -- -y --profile minimal --default-toolchain ${RUST_VERSION} && \
    chmod -R a+w $RUSTUP_HOME $CARGO_HOME && \
    rustc --version && cargo --version

RUN pie install jaapio/pagefind:dev-main@dev

RUN docker-php-ext-configure pcntl --enable-pcntl \
  && docker-php-ext-install pcntl \
  && pie install arnaud-lb/inotify

RUN rm -rf /usr/local/cargo/registry /usr/local/cargo/git

FROM php:8.4-cli AS application

COPY --from=builder /usr/local/lib/php /usr/local/lib/php

RUN echo "extension=pagefind" > /usr/local/etc/php/conf.d/pagefind.ini

RUN export DEBIAN_FRONTEND="noninteractive"; \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.json

RUN composer install --no-dev --optimize-autoloader

# Production stage
FROM php:8.4-cli AS production

WORKDIR /app

COPY --from=builder /usr/local/lib/php /usr/local/lib/php
COPY --from=application /app /app

RUN export DEBIAN_FRONTEND="noninteractive"; \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libclang1 libzip-dev; \
    rm -rf /var/lib/apt/lists/*

RUN echo "extension=pagefind" > /usr/local/etc/php/conf.d/pagefind.ini
RUN docker-php-ext-install zip

ENTRYPOINT ["php", "/app/vendor/bin/guides"]
