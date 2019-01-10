#!/bin/sh

set -e

apache2-background

php /var/www/init.php

SS_DIR=/var/www/shadowsocks

su - www-data -c "ulimit -n 1048576 && ${SS_DIR}/outline-ss-server -metrics 0.0.0.0:9090 -config ${SS_DIR}/cfg/config.yml"
