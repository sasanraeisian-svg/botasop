FROM php:8.2-apache
RUN apt-get update && apt-get install -y libcurl4-openssl-dev && docker-php-ext-install pdo pdo_mysql curl
COPY daramad_edit/ /var/www/html/
EXPOSE 80
CMD ["bash", "-lc", "a2dismod mpm_event mpm_worker || true; rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true; a2enmod mpm_prefork; exec apache2-foreground"]
