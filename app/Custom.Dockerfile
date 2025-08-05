FROM phpdocumentor-guides

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN composer require phpdocumentor/guides-code phpdocumentor/guides-theme-bootstrap -W --prefer-dist

COPY ./packages/guides-search /tmp/guides-search

RUN composer config repositories.phpdocumentor/guides-search path /tmp/guides-search && \
    composer require phpdocumentor/guides-search:dev-main@dev
