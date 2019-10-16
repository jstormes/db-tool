FROM php:7


############################################################################
# Install requried libraries, should be the same across dev, QA, etc...
############################################################################
RUN apt-get -y update \
    && apt-get install -y curl zip unzip libzip-dev inetutils-ping iproute2 \
    && docker-php-ext-install zip pdo pdo_mysql

RUN apt-get install -y wget git \
    && wget https://getcomposer.org/installer \
    && php installer \
    && rm installer \
    && mv composer.phar /usr/local/bin/composer \
    && chmod u+x /usr/local/bin/composer

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on"  >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=default-docker" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY bin/DockerStartUp.sh /root/bin/DockerStartUp.sh
RUN chmod a+x /root/bin/DockerStartUp.sh

ENV PATH /var/www/src:/var/www/vendor/bin:/var/www/bin:/root/bin:root/.composer/vendor/bin:$PATH

WORKDIR /var/www

CMD ["sh", "/root/bin/DockerStartUp.sh"]
