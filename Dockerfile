FROM php:8.2-fpm

# Dependências de sistema e extensões PHP
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

# Instala Node 20 (pra build do Vite / frontend)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

# 1) Instala dependências PHP (SEM scripts do composer)
COPY composer.json composer.lock* ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# 2) Instala dependências Node e faz build dos assets
COPY package.json package-lock.json* vite.config.* ./
RUN [ -f package.json ] && npm install && npm run build || echo "Nenhum frontend para buildar"

# 3) Copia o resto do código
COPY . .

EXPOSE 8000

# 4) Ao subir o container:
#    - tenta rodar package:discover (se falhar, segue)
#    - roda migrations
#    - sobe o servidor
CMD ["sh", "-c", "php artisan package:discover --ansi || true; php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
