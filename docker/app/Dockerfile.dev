FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    vim \
    netcat-openbsd \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    && docker-php-ext-install zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql pdo_pgsql pgsql zip mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN ls -la && cat composer.json || echo "composer.json não encontrado"

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html

COPY docker/app/entrypoint.dev.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && mkdir -p storage/logs bootstrap/cache

COPY composer.json composer.lock* ./

RUN composer install --no-interaction
RUN npm install

COPY package*.json ./

RUN npm install


EXPOSE 8001

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]