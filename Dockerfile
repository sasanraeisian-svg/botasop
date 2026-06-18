FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
RUN a2enmod mpm_prefork
COPY daramad_edit/ /var/www/html/
EXPOSE 80
