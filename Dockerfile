FROM php:8.3-fpm-alpine3.20

RUN apk update && apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    mysql-client \
    python3 \
    py3-pip \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

WORKDIR /var/www

COPY . /var/www

COPY setup_venv.sh /var/www/setup_venv.sh

RUN chmod +x /var/www/setup_venv.sh

EXPOSE 9000

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

CMD ["/var/www/setup_venv.sh", "&&", "php-fpm"]
