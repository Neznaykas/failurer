FROM php:7.4.16-fpm
LABEL maintainer="Sergey Snopko"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . ./

ENV XDEBUG_MODE=coverage

#for Dev
RUN apt-get update && apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/html/

#CMD [ "php", "example/index.php" ]
#EXPOSE 3000