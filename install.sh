#!/bin/sh

VIP=$(echo $1 | tr [A-Z] [a-z])
echo "identify ${VIP:-free} server"

VERSION=$(curl -sL https://raw.githubusercontent.com/syncxplus/shadowbox2/master/html/version)

docker ps -a | grep -e shadowbox -e inline | awk '{print $1}' | xargs docker rm -f -v

set -e
if [[ -z "${VIP}" || "${VIP}" != "vip" ]]; then
  docker run --name shadowbox --restart always -d --net host -v $PWD/shadowbox:/var/www/shadowsocks/cfg syncxplus/shadowbox2:${VERSION}
else
  docker run --name shadowbox --restart always -d -e EXCLUSIVE=true --net host -v $PWD/shadowbox:/var/www/shadowsocks/cfg syncxplus/shadowbox2:${VERSION}
fi
