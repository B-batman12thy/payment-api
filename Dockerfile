# --- Étape vendor : installe les dépendances Composer en prod
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# --- Étape runtime : PHP 8.3 + Apache
FROM php:8.3-apache
WORKDIR /var/www/html

# Extensions PHP nécessaires
RUN apt-get update && apt-get install -y libpq-dev libzip-dev unzip \
  && docker-php-ext-install pdo pdo_pgsql zip \
  && a2enmod rewrite

# Apache pointe sur public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf

# Copie du code depuis l'étape vendor
COPY --from=vendor /app /var/www/html

# Permissions storage/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
