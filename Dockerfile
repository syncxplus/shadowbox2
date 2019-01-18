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

RUN mkdir /var/www/shadowsocks && cd /var/www/shadowsocks \
 && SS_VER=$(curl -s https://api.github.com/repos/Jigsaw-Code/outline-ss-server/releases/latest | grep tag_name | awk '{print $2}' | sed 's/[",]//g') \
 && curl -sL https://github.com/Jigsaw-Code/outline-ss-server/releases/download/${SS_VER}/outline-ss-server_${SS_VER##*v}_linux_x86_64.tar.gz | tar xzv

USER root

RUN usermod -s /bin/bash www-data
