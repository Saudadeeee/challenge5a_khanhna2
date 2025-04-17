
FROM php:8.1-apache


RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/avatars \
    /var/www/html/uploads/assignments \
    /var/www/html/uploads/submissions \
    /var/www/html/uploads/challenges \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/uploads

EXPOSE 80

# Chạy Apache
CMD ["apache2-foreground"]
