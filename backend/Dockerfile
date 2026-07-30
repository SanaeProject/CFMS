FROM php:8.3-apache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip pdo_mysql

WORKDIR /var/www/html
COPY composer.json composer.lock ./
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader