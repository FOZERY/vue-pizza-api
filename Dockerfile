FROM php:8.1-apache

WORKDIR /var/www/html

RUN apt-get update

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/run/apache2
RUN chown -R www-data:www-data /var/log/apache2

# Adjust directory permissions
RUN chmod -R 755 /var/www/html

EXPOSE 80

RUN docker-php-ext-install mysqli pdo pdo_mysql
