FROM php:5.6.22-fpm-alpine
RUN apk upgrade --update && apk add \
  coreutils \
  freetype-dev \
  libjpeg-turbo-dev \
  libltdl \
  libmcrypt-dev \
  libpng-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install -j5 mysql gd ldap soap zip
ADD scripts/install-composer.sh /opt/install-composer.sh
RUN /opt/install-composer.sh && mv /var/www/html/composer.phar /usr/local/bin/composer
