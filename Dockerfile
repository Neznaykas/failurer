FROM php:7.4.16-fpm
LABEL maintainer="Sergey Snopko"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . .

ENV PHP_EXTRA_CONFIGURE_ARGS --enable-fpm --with-fpm-user=www-data --with-fpm-group=www-data 

#for Dev
RUN apt-get update && apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip

WORKDIR /var/www/html/

#CMD [ "php", "example/index.php" ]
#EXPOSE 3000