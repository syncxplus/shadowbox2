#!/bin/sh

[[ ! -d "${PWD}/shadowbox" ]] && mkdir ${PWD}/shadowbox
[[ ! -f "${PWD}/shadowbox/config.yml" ]] && {
  curl -kL https://raw.githubusercontent.com/syncxplus/outline-ss-server/ufo/scripts/config.yml -o ${PWD}/shadowbox/config.yml
}

image=syncxplus/shadowbox2
tags=`curl https://registry.hub.docker.com/v1/repositories/${image}/tags | sed -e 's/[][]//g' -e 's/"//g' -e 's/ //g' | tr '}' '\n' | awk -F: '{print $3}'`

docker ps -a | grep shadowbox | awk '{print $1}' | xargs -I {} docker rm -f -v {}
docker run --name shadowbox --restart always -d --net host -v ${PWD}/shadowbox:/shadowbox ${image}:`echo "${tags}" | awk 'END{print $0}'`
