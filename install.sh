#!/bin/sh

docker ps -a | grep shadowbox | awk '{print $1}' | xargs -I {} docker rm -f -v {}
docker images -a | grep shadowbox | awk '{print $3}' | xargs -I {} docker rmi {}

set -e

VERSION=$(curl -sL https://raw.githubusercontent.com/syncxplus/shadowbox2/master/html/version)
docker run --name shadowbox --restart always -d -e EXCLUSIVE=true --net host -v $PWD/shadowbox:/var/www/shadowsocks/cfg syncxplus/shadowbox2:${VERSION}
