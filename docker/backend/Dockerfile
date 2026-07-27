FROM php:8.4-cli-alpine

RUN apk add --no-cache \
        git \
        libpq-dev \
        oniguruma-dev \
        sqlite-dev \
        unzip \
        $PHPIZE_DEPS \
    && docker-php-ext-install \
        mbstring \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]

