FROM composer:2.8 AS composer

FROM php:8.3-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libonig-dev libzip-dev unzip \
    && docker-php-ext-install mbstring pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

COPY --chown=www-data:www-data . .

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html

USER www-data

CMD ["php-fpm"]
