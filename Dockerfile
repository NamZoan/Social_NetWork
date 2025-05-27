FROM php:8.2-fpm

# Cài đặt extension PHP và các package cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    npm

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy code vào container
COPY . .

# Tạo thư mục nếu chưa có (phòng trường hợp clone code mới)
RUN mkdir -p storage bootstrap/cache

# Phân quyền cho storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Cài đặt Composer dependencies (nếu muốn tự động khi build)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Nếu muốn build FE luôn thì mở 2 dòng sau (nếu không thì ignore)
# RUN npm install
# RUN npm run build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
