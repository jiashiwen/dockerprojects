#!/bin/sh
cd nginximage
docker build -t app-nginx:1.12 .

cd ../php-fpm7.1
docker build -t app-php-fpm:7.1 .
