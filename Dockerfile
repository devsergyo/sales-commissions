FROM php:8.4-fpm

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring gd

# Instala e habilita a extensão Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia o arquivo .env.example para .env se o .env não existir
COPY .env.example /var/www/.env.example
RUN if [ ! -f "/var/www/.env" ]; then cp /var/www/.env.example /var/www/.env; fi

CMD ["php-fpm"]
