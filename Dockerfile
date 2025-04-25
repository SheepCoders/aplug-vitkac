FROM php:8.3-fpm

RUN apt update && apt install -y \
    chromium \
    python3 \
    python3-pip \
    python3-venv \
    curl \
    unzip \
    ca-certificates \
    git \
    bash \
    wget \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    default-mysql-client \
    libx11-xcb1 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libasound2 \
    libatk-bridge2.0-0 \
    libgtk-3-0 \
    libnss3 \
    libxss1 \
    fonts-liberation \
    && apt clean

RUN docker-php-ext-install pdo pdo_mysql zip gd \
    && docker-php-ext-enable pdo_mysql

WORKDIR /var/www

COPY . /var/www

COPY setup_venv.sh /var/www/setup_venv.sh

RUN chmod +x /var/www/setup_venv.sh

EXPOSE 9000

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

CMD ["/var/www/setup_venv.sh", "&&", "php-fpm"]
