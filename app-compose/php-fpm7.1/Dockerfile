# Tag: search.leju.com-php7-fpm 

FROM php:7.1-fpm 

MAINTAINER Jiashiwen "shiwen4@leju.com"

ADD ./envconfig /envconfig
RUN mkdir -p /search.leju.com

#Install php extention
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libmemcached-dev \
        curl \
    && docker-php-ext-install iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \   
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install mysqli 

#Install redis module
#RUN pecl install redis \
#    && docker-php-ext-enable redis
RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/3.1.4.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis

# Install memcache extension
#RUN pecl install memcached-3.0.4 \
#    && docker-php-ext-enable memcached
#RUN curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/v3.0.4.tar.gz" \
#    && mkdir -p /usr/src/php/ext/memcached \
#    && tar -C /usr/src/php/ext/memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
#    && docker-php-ext-configure memcached \
#    && docker-php-ext-install memcached \
#    && rm -f /tmp/memcached.tar.gz \

# Install PECL extensions xdebug
#RUN pecl install xdebug
#RUN docker-php-ext-enable xdebug

#Clean apt
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

EXPOSE 9000 19000 

CMD ["php-fpm"]
