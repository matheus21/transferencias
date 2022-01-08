FROM php:7.4.27-fpm

ENV ROOT_PATH=/var/www

WORKDIR $ROOT_PATH

RUN apt update \
    && chmod -R 777 $ROOT_PATH

#Instalação Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer

#Instalação Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

#Instalação extensões Mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

COPY . /var/www

EXPOSE 8021

CMD "composer install"
CMD "php artisan serve --host=0.0.0.0"