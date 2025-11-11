FROM php:8.2-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    npm \
    build-essential \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-enable pdo_pgsql

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

# Copia o código
COPY . .

# Instala dependências PHP
RUN composer install --no-interaction --optimize-autoloader

# Instala dependências Node (mas não roda npm run dev)
RUN npm install

EXPOSE 8000

# Comando do container PHP
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
