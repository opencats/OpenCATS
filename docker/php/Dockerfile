FROM php:5.6.27-fpm-alpine
RUN apk upgrade --update && apk add \
  coreutils \
  freetype-dev \
  libjpeg-turbo-dev \
  libltdl \
  libmcrypt-dev \
  libpng-dev \
  antiword \
  poppler-utils \
  html2text \
  alpine-sdk \
  autoconf \
  automake
RUN cd ~ && \
  wget http://www.gnu.org/software/unrtf/unrtf-0.21.9.tar.gz && \
  tar xzvf unrtf-0.21.9.tar.gz && \
  cd unrtf-0.21.9/ && \
  ./bootstrap && \
  ./configure && \
  make && \
  make install
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j5 mysql gd ldap soap zip
ADD scripts/install-composer.sh /opt/install-composer.sh
RUN dos2unix /opt/install-composer.sh && \
  chmod +x /opt/install-composer.sh && \
  /opt/install-composer.sh && \
  mv /var/www/html/composer.phar /usr/local/bin/composer
ENV DOCKERIZE_VERSION v0.2.0
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz
