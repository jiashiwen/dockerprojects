FROM  nginx:1.13.9

LABEL maintainer="jiashiwen126@126.com"
RUN set -x \
	&& apt-get update \
	&& apt-get install --no-install-recommends --no-install-suggests -y curl iputils-ping

#Clean apt
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

