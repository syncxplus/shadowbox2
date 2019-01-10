#!/bin/sh

SS_VERSION=$(curl -sL https://raw.githubusercontent.com/syncxplus/shadowbox2/master/cfg/system.ini | grep SS_VERSION)

docker ps -a | grep -e shadowbox -e inline | awk '{print $1}' | xargs docker rm -f -v

docker rmi syncxplus/shadowbox2:${SS_VERSION##*=}

set -e
docker run --name shadowbox --restart always -d -e EXCLUSIVE=true --net host -v $PWD/shadowbox:/var/www/shadowsocks/cfg syncxplus/shadowbox2:${SS_VERSION##*=}
