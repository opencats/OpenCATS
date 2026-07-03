ARG PHP_VERSION=8.4.1
FROM php:${PHP_VERSION}-fpm-alpine

# Runtime/system packages required by OpenCATS and helper tools.
RUN apk add --no-cache \
  antiword \
  coreutils \
  html2text \
  libltdl \
  poppler-utils \
  wget

# Build dependencies for PHP extensions and external tools.
RUN apk add --no-cache \
  alpine-sdk \
  autoconf \
  automake \
  freetype-dev \
  libjpeg-turbo-dev \
  libmcrypt-dev \
  oniguruma-dev \
  libpng-dev \
  libxml2-dev \
  libzip-dev \
  openldap-dev

# External tools.
RUN cd /tmp && \
  wget https://ftp.gnu.org/gnu/unrtf/unrtf-0.21.10.tar.gz && \
  tar xzvf unrtf-0.21.10.tar.gz && \
  cd unrtf-0.21.10/ && \
  export CFLAGS="-D_GNU_SOURCE -std=gnu89 -Wno-error=implicit-int -Wno-error=implicit-function-declaration" && \
  ./bootstrap && \
  ./configure && \
  make && \
  make install

# PHP extensions expected by OpenCATS.
RUN pecl install mcrypt-1.0.9 && docker-php-ext-enable mcrypt
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli gd soap zip ldap mbstring

# Composer and helper binaries.
COPY docker/php/scripts/install-composer.sh /opt/install-composer.sh
RUN chmod +x /opt/install-composer.sh && \
  /opt/install-composer.sh && \
  mv /var/www/html/composer.phar /usr/local/bin/composer
ENV DOCKERIZE_VERSION=v0.2.0
RUN wget https://github.com/jwilder/dockerize/releases/download/${DOCKERIZE_VERSION}/dockerize-linux-amd64-${DOCKERIZE_VERSION}.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-${DOCKERIZE_VERSION}.tar.gz
