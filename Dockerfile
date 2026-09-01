FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/laravel/public

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

RUN { \
    echo "upload_max_filesize=256M"; \
    echo "post_max_size=256M"; \
    echo "max_file_uploads=50"; \
    echo "memory_limit=512M"; \
    echo "max_execution_time=300"; \
} > /usr/local/etc/php/conf.d/uploads.ini

RUN a2enmod rewrite

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN printf '%s\n' \
    'Alias /ppd /var/www/html/ppd' \
    '<Directory /var/www/html/ppd>' \
    '    Options Indexes FollowSymLinks' \
    '    AllowOverride All' \
    '    Require all granted' \
    '    DirectoryIndex index.php index.html' \
    '</Directory>' \
    > /etc/apache2/conf-available/ppd-alias.conf \
    && a2enconf ppd-alias
