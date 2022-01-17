FROM composer:2 as build
ADD . .
RUN composer install --no-dev

FROM php:8.1-apache as base
RUN apt-get update && apt-get install -y libzip-dev zip libgmp-dev
RUN pecl install redis && docker-php-ext-enable redis
RUN docker-php-ext-install pdo_mysql gettext zip pcntl gmp
RUN a2enmod headers rewrite
COPY --chown=www-data:www-data --from=build /app .

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

#FROM base as dev
#RUN docker-php-ext-install xdebug
#RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

FROM base as prod
RUN mv php-production.ini "$PHP_INI_DIR/php.ini"
