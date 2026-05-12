FROM php:8.4-fpm-alpine

# System packages for the extensions we need
RUN apk add --no-cache \
        bash \
        git \
        unzip \
        icu-dev \
        libzip-dev \
        zip \
        oniguruma-dev \
        libpq-dev \
        postgresql-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/* /tmp/*

# Composer (pinned, copied from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/zz-erp.ini

WORKDIR /var/www/html

# Run as non-root www-data (already exists in the base image)
RUN chown -R www-data:www-data /var/www/html
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
