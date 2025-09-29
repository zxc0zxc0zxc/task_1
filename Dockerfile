ARG COMPOSER_VERSION=2

FROM php:8.3-fpm AS builder

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
     git unzip curl libzip-dev libpng-dev libonig-dev libicu-dev libxml2-dev libpq-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql zip bcmath mbstring xml gd intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-progress --prefer-dist --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN composer dump-autoload --optimize
FROM php:8.3-fpm


RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libicu-dev \
    libxml2-dev \
    libpq-dev \
    gcc \
    make \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    bcmath \
    mbstring \
    xml \
    gd \
    intl \
    opcache \
    && pecl install -o -f redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*


COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app

COPY --from=builder /app /app

RUN groupadd -g 1000 app \
    && useradd -u 1000 -g app -m -d /home/app -s /bin/bash app \
    && mkdir -p /var/run/php \
    && chown -R app:app /var/run/php

RUN mkdir -p /app/storage /app/bootstrap/cache /app/storage/logs \
    && chown -R app:app /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache


COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

VOLUME [ "/app/storage", "/app/bootstrap/cache" ]

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
