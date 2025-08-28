# syntax=docker/dockerfile:1

########### Etape 1 : Build vendor avec Composer ###########
FROM composer:2 AS vendor
WORKDIR /app

# Installer les deps au plus tôt pour profiter du cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

# Copier le reste du code
COPY . .

# Optimiser l'autoloader
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

########### Etape 2 : Runtime PHP 8.3 + Apache ###########
FROM php:8.3-apache
WORKDIR /var/www/html

# Extensions PHP (PostgreSQL/Zip) + rewrite
RUN apt-get update && apt-get install -y libpq-dev libzip-dev unzip \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && a2enmod rewrite

# DocumentRoot = public et AllowOverride All pour que .htaccess fonctionne
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot ${APACHE_DOCUMENT_ROOT}#' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's#<Directory /var/www/>#<Directory ${APACHE_DOCUMENT_ROOT}/>#' /etc/apache2/apache2.conf \
 && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copier l'appli construite par l'étape vendor
COPY --from=vendor /app /var/www/html

# Droits/permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && find storage -type d -exec chmod 775 {} \; \
 && find storage -type f -exec chmod 664 {} \; \
 && chmod -R 775 bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
