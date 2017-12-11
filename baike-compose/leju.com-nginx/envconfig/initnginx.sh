#!/bin/sh

# initnginx enviroment! 

`rm -f /etc/nginx/conf.d/*.conf && cp /envconfig/nginx/conf.d/*.conf /etc/nginx/conf.d/`
`cat  /envconfig/nginx/fastcgi_params >> /etc/nginx/fastcgi_params`
`/usr/sbin/nginx -g "daemon off;"`
