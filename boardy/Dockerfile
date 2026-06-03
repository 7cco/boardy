FROM php:8.3-fpm 

RUN apt-get update && apt-get install -y \ 
    git curl libpng-dev libonig-dev libxml2-dev \ 
    zip unzip libzip-dev \ 
    && rm -rf /var/lib/apt/lists/* 

RUN docker-php-ext-install pdo_mysql mbstring zip opcache 
RUN pecl install redis && docker-php-ext-enable redis 

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer 

WORKDIR /var/www/html 

COPY composer.json composer.lock ./ 

RUN composer install --no-dev --no-scripts --no-autoloader 

COPY . . 
RUN composer dump-autoload --optimize 

RUN chown -R www-data:www-data storage bootstrap/cache 

RUN chmod -R 775 storage bootstrap/cache 

EXPOSE 9000 

CMD ["php-fpm"] 