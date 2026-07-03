FROM php:8.2-apache

# Instal ekstensi database MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# aktifkan modul rewrite apache
RUN a2enmod rewrite

# Salin file proyek ke web root
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
