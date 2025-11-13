### Stage 1 - Front-end assets
FROM node:20-alpine AS node_build
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js ./ 
RUN npm run build

### Stage 2 - Composer dependencies
FROM composer:2.7 AS vendor_build
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

### Stage 3 - Runtime image
FROM php:8.2-fpm

ENV PHP_MEMORY_LIMIT=256M

RUN apt-get update && apt-get install -y \
        git \
        curl \
        unzip \
        zip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        exif \
        pcntl \
        pdo_mysql \
        mbstring \
        gd \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY --from=vendor_build /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=vendor_build /app/vendor ./vendor
COPY --from=node_build /app/public/build ./public/build

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
