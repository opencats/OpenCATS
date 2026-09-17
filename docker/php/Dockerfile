ARG PHP_VERSION=8.4.1
FROM php:${PHP_VERSION}-fpm-alpine

# Runtime/system packages required by the test environment.
RUN apk add --no-cache \
  coreutils \
  wget

# Build dependencies for PHP extensions.
RUN apk add --no-cache \
  alpine-sdk \
  autoconf \
  oniguruma-dev

# PHP extensions currently required by the test environment.
RUN docker-php-ext-install -j"$(nproc)" mysqli mbstring

# Composer and helper binaries.
COPY docker/php/scripts/install-composer.sh /opt/install-composer.sh
RUN chmod +x /opt/install-composer.sh && \
  /opt/install-composer.sh && \
  mv /var/www/html/composer.phar /usr/local/bin/composer

ENV DOCKERIZE_VERSION=v0.2.0
RUN wget https://github.com/jwilder/dockerize/releases/download/${DOCKERIZE_VERSION}/dockerize-linux-amd64-${DOCKERIZE_VERSION}.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-${DOCKERIZE_VERSION}.tar.gz
