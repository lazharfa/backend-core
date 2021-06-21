FROM php:7.2-fpm
RUN apt-get update && apt-get install -y \ 
    git \
    libzip-dev \
    zip \
    unzip  

RUN apt-get update -y && apt-get install -y libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6-dev
RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev

RUN docker-php-ext-install mbstring
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install pdo_mysql zip 
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && composer global require hirak/prestissimo --no-plugins --no-scripts
RUN composer global require hirak/prestissimo

RUN docker-php-ext-configure gd --with-gd --with-webp-dir --with-jpeg-dir \
    --with-png-dir --with-zlib-dir --with-xpm-dir --with-freetype-dir 

RUN docker-php-ext-install gd

WORKDIR /var/www/html  
COPY . .   

RUN chown -R www-data:www-data /var/www
RUN chmod -R 777 /var/www/html 
RUN chmod -R o+w /var/www/html/storage
 
RUN composer install \
    --no-dev \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist
