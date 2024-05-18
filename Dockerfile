FROM php:8.1-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/run/apache2
RUN chown -R www-data:www-data /var/log/apache2

# Adjust directory permissions
RUN chmod -R 777 /var/www/html

EXPOSE 80

