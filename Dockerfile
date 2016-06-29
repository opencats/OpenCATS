FROM php:5.5-apache

RUN apt-get update && \
    apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libncurses5-dev \
        libicu-dev \
        libmemcached-dev \
        libcurl4-openssl-dev \
		libpng-dev \
        libpng12-dev \
        libgmp-dev \
        libxml2-dev \
		libldap2-dev \
		php-soap \
        curl \
        zlib1g-dev \
        ssmtp

RUN apt-get install -y \
		antiword \
		poppler-utils \
		html2text \
		unrtf

RUN rm -rf /var/lib/apt/lists/* 
	
RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h

RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-configure mysql --with-mysql=mysqlnd && \
    docker-php-ext-configure mysqli --with-mysqli=mysqlnd && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install mysql && \
    docker-php-ext-install ldap && \
    docker-php-ext-install soap && \
    docker-php-ext-install intl && \
    docker-php-ext-install mcrypt && \
    docker-php-ext-install gd && \
    docker-php-ext-install gmp && \
    docker-php-ext-install zip

COPY . /var/www/html

WORKDIR /var/www/html

EXPOSE 80
EXPOSE 443
