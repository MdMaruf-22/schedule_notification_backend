# ---------- Base Image ----------
FROM php:8.2-apache

# ---------- System Dependencies ----------
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite (for Laravel routes)
RUN a2enmod rewrite

# ---------- Copy Project ----------
WORKDIR /var/www/html
COPY . .

# ---------- Composer ----------
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ---------- Permissions ----------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# ---------- Apache Config ----------
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>" > /etc/apache2/conf-available/laravel.conf && a2enconf laravel

EXPOSE 8080
CMD ["apache2-foreground"]
