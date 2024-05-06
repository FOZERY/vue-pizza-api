FROM php:8.1-apache

WORKDIR /var/www/html

RUN apt-get update

COPY . /var/www/html

EXPOSE 80

RUN docker-php-ext-install mysqli pdo pdo_mysql
