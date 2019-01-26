FROM syncxplus/php:7.3.1-apache-stretch

COPY cfg /var/www/cfg/

COPY html /var/www/html/

COPY src /var/www/src/

COPY bootstrap.php composer.json init.php /var/www/

COPY apache2-background.sh /usr/local/bin/apache2-background

COPY entrypoint.sh /usr/local/bin/docker-php-entrypoint

COPY ports.conf /etc/apache2/ports.conf

RUN chown -R www-data:www-data /var/www

USER www-data

ARG GITHUB_TOKEN

RUN composer config --global --auth github-oauth.github.com ${GITHUB_TOKEN} \
 && composer install -d /var/www --prefer-dist --optimize-autoloader \
 && composer clear-cache

ARG SS_VER=v1.0.3-syncxplus-1

RUN mkdir /var/www/shadowsocks \
 && cd /var/www/shadowsocks \
 && curl -sL https://github.com/syncxplus/outline-ss-server/releases/download/${SS_VER}/outline-ss-server_${SS_VER##*v}_linux_`uname -m`.tar.gz | tar xzv

USER root

RUN usermod -s /bin/bash www-data
