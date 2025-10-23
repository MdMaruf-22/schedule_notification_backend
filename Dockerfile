# ---------- Base Image ----------
FROM php:8.2-apache

# ---------- System Dependencies ----------
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# ---------- Enable Apache mod_rewrite ----------
RUN a2enmod rewrite

# ---------- Set Working Directory ----------
WORKDIR /var/www/html

# ---------- Copy Project ----------
COPY . .

# ---------- Composer ----------
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ---------- Permissions ----------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# ---------- Apache Config ----------
# Enable .htaccess in public folder
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Set DocumentRoot to Laravel public folder
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# ---------- Expose Port ----------
EXPOSE 80

# ---------- Start Apache ----------
CMD ["apache2-foreground"]
