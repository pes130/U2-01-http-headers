FROM php:8.4.12-apache

# Copia el código
COPY web/ /var/www/html/

# Habilita mod_headers para jugar con cabeceras desde Apache
RUN a2enmod headers

# (Opcional) ajustes de PHP/Apache mínimos
# RUN echo "expose_php=0" > /usr/local/etc/php/conf.d/security.ini

EXPOSE 80