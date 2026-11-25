FROM php:8.4-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libzip-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-install dom pdo pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
