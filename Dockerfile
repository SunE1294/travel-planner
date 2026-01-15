FROM php:8.2-apache

# 1. Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql zip

# 2. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 3. Set Document Root to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy application files
COPY . /var/www/html

# 6. Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 7. Create necessary folders
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Create SQLite database file
RUN touch /var/www/html/database/database.sqlite

# 9. Set permissions
RUN chown -R www-data:www-data /var/www/html

# 10. Expose Port
EXPOSE 80

# 11. START COMMAND (Modified to Auto-Migrate Database)
CMD bash -c "php artisan migrate --force && apache2-foreground"
