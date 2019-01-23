#!/bin/sh

VIP=$(echo $1 | tr [A-Z] [a-z])
echo "identify ${VIP:-free} server"

SS_VERSION=$(curl -sL https://raw.githubusercontent.com/syncxplus/shadowbox2/master/cfg/system.ini | grep SS_VERSION)

VERSION=${SS_VERSION##*=}

docker ps -a | grep -e shadowbox -e inline | awk '{print $1}' | xargs docker rm -f -v

set -e
if [[ -z "${VIP}" || "${VIP}" != "vip" ]]; then
  docker run --name shadowbox --restart always -d --net host -v $PWD/shadowbox:/var/www/shadowsocks/cfg syncxplus/shadowbox2:${VERSION}
else
  docker run --name shadowbox --restart always -d -e EXCLUSIVE=true --net host -v $PWD/shadowbox:/var/www/shadowsocks/cfg syncxplus/shadowbox2:${VERSION}
fi
