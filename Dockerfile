FROM php:8.2-fpm

# Dependências e extensões PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    build-essential \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-enable pdo_pgsql

# Instala Node 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

# Copia tudo primeiro
COPY . .

# Instala dependências PHP (sem scripts pra evitar erros)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# Instala dependências Node e faz o build do Vite
RUN [ -f package.json ] && npm install && npm run build || echo "Nenhum frontend para buildar"

# Garante que o manifest foi criado
RUN ls -la public/build || (echo "⚠️ Erro: build do Vite não gerou manifest.json" && exit 1)

EXPOSE 8000

# Comando final: descobre pacotes, faz migrate e sobe app
CMD ["sh", "-c", "php artisan package:discover --ansi || true; php artisan migrate --force; php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
