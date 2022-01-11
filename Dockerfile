FROM php:7.4.27-fpm

ENV ROOT_PATH=/var/www

WORKDIR $ROOT_PATH

RUN apt update \
    && chmod -R 777 $ROOT_PATH \
    && apt install unzip \
    && apt install -y wget \
    && apt install -y gpg

#Instalação infection
RUN wget https://github.com/infection/infection/releases/download/0.26.0/infection.phar \
    && wget https://github.com/infection/infection/releases/download/0.26.0/infection.phar.asc \
    && chmod +x infection.phar \
    && gpg --recv-keys C6D76C329EBADE2FB9C458CFC5095986493B4AA0 \
    && gpg --with-fingerprint --verify infection.phar.asc infection.phar \
    && mv infection.phar /usr/local/bin/infection

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

CMD bash -c "composer install && php artisan serve --host=0.0.0.0"