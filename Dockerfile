FROM php:7.2.6

RUN apt-get update && apt-get install libpng-dev curl git libyaml-dev -y
RUN pecl install yaml-2.0.0 -y && docker-php-ext-enable yaml
RUN docker-php-ext-install pdo pdo_mysql gd
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

RUN mkdir /usr/src/app
WORKDIR /usr/src/app

COPY composer.json /usr/src/app
RUN composer install
RUN composer global require phpunit/phpunit
RUN export PATH=~/.composer/vendor/bin:$PATH

COPY . /usr/src/app

CMD while true; do sleep 1000; done
