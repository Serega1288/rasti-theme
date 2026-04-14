FROM wordpress:php8.2-apache

RUN sed -i 's/^Listen 80$/Listen 80\nListen 8080/' /etc/apache2/ports.conf \
    && sed -i 's#<VirtualHost \\*:80>#<VirtualHost *:80 *:8080>#' /etc/apache2/sites-available/000-default.conf

COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/php/wordpress-dev.ini /usr/local/etc/php/conf.d/wordpress-dev.ini
