FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY daramad_edit/ /var/www/html/
EXPOSE 80
