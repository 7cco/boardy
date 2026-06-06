# Stage 1: Build frontend assets with Node.js
FROM node:20-alpine AS frontend

WORKDIR /var/www/html

# Копируем package файлы
COPY package*.json ./

# Устанавливаем зависимости
RUN npm ci

# Копируем исходники
COPY . .

# Собираем production ассеты
RUN npm run build

# Stage 2: PHP-FPM с готовыми ассетами
FROM php:8.3-fpm

WORKDIR /var/www/html

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем PHP расширения
RUN docker-php-ext-install pdo_mysql mbstring zip opcache
RUN pecl install redis && docker-php-ext-enable redis

# Копируем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем composer файлы и устанавливаем зависимости
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

# Копируем весь проект
COPY . .

# Копируем собранные ассеты из frontend stage
COPY --from=frontend /var/www/html/public/build ./public/build

# Генерируем autoloader
RUN composer dump-autoload --optimize

# Устанавливаем права
RUN chown -R www-data:www-data storage bootstrap/cache public/build
RUN chmod -R 775 storage bootstrap/cache public/build

EXPOSE 9000

CMD ["php-fpm"]