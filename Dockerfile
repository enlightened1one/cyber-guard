FROM php:8.3-apache

RUN docker-php-ext-install pdo_sqlite
RUN a2enmod rewrite headers

WORKDIR /var/www/html
COPY . /var/www/html

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/cyberguard-start
RUN chmod +x /usr/local/bin/cyberguard-start \
    && mkdir -p /var/www/html/storage/submissions \
    && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80
CMD ["cyberguard-start"]
