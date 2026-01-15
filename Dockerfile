FROM php:8.2-apache

# ১. সিস্টেম ডিপেন্ডেন্সি ইনস্টল
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    && docker-php-ext-install pdo pdo_mysql zip

# ২. Apache Rewrite এনাবেল করা
RUN a2enmod rewrite

# ৩. Apache এর রুট ফোল্ডার Public এ সেট করা
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

# ৪. মেমোরি অপ্টিমাইজ করে Composer ইনস্টল
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts

# ৫. স্টোরেজ ফোল্ডার এবং SQLite ডাটাবেস তৈরি
RUN mkdir -p storage bootstrap/cache database
RUN touch database/database.sqlite

# ৬. পারমিশন সেট করা (খুবই গুরুত্বপূর্ণ)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

EXPOSE 80

# ৭. সার্ভার চালু করার আগে মাইগ্রেশন রান করা
CMD bash -c "chmod 666 database/database.sqlite && php artisan migrate --force && apache2-foreground"
