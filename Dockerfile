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

# 1) Instala dependências PHP
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# 2) Instala dependências Node e faz build dos assets
COPY package.json package-lock.json* vite.config.* ./
RUN [ -f package.json ] && npm install && npm run build || echo "Nenhum frontend para buildar"

# 3) Copia o resto do código
COPY . .

# Opcional: otimizações do Laravel
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
