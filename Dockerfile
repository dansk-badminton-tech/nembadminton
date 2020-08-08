FROM php:7.4-apache as base

RUN docker-php-ext-install pdo pdo_mysql gettext
RUN a2enmod headers rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
ADD --chown=www-data:www-data . .

FROM base as dev
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

FROM base as prod
RUN mv php-production.ini "$PHP_INI_DIR/php.ini"