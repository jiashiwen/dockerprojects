#!/bin/sh

# init php-fpm  enviroment!"`

`rm -f /usr/local/etc/php-fpm.conf && cp /envconfig/php/php-fpm.conf /usr/local/etc/php-fpm.conf`

`/usr/local/sbin/php-fpm -c /envconfig/php/php.ini`
