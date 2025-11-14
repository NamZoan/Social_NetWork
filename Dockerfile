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

# --- THAY ĐỔI 1: Thêm --no-scripts ---
# Báo Composer không chạy bất kỳ script nào (như artisan package:discover)
# vì chúng ta chưa có file artisan ở stage này.
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

### Stage 3 - Runtime image
FROM php:8.2-fpm

ENV PHP_MEMORY_LIMIT=256M

# Cài đặt các extensions PHP
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

# Copy composer binary từ stage trước
COPY --from=vendor_build /usr/bin/composer /usr/bin/composer

# Copy toàn bộ code ứng dụng
COPY . .

# Copy các dependencies đã được build, ghi đè lên thư mục vendor và public/build
COPY --from=vendor_build /app/vendor ./vendor
COPY --from=node_build /app/public/build ./public/build

# --- THAY ĐỔI 2: Chạy các script tối ưu hóa ---
# Chạy package:discover một cách an toàn. Bằng cách tạm thời đặt BROADCAST_DRIVER=log,
# chúng ta tránh được lỗi khởi tạo Pusher/Reverb khi chưa có biến môi trường.
RUN BROADCAST_DRIVER=log composer run-script post-autoload-dump --no-dev

# Lệnh 'view:cache' vẫn an toàn, có thể giữ lại:
RUN php artisan view:cache

# Thiết lập quyền cho storage và cache
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# Lệnh CMD này dùng 'artisan serve' (tốt cho Render/Fly)
# Đối với FPM production thực thụ, bạn sẽ dùng 'php-fpm'
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]