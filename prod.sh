#!/bin/bash

export COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1 
export MEMBERENV="prod"
export PROJECT=${PWD}

port=8800
nginxconf=""
for file in members/*; do
  if [[ -d "$file" && ! -L "$file" ]]; then
    newport=$((port++))
    dirname=$file
    member="${dirname%"${dirname##*[!/]}"}"
    member="${member##*/}"   
    nginxconf=$"$nginxconf   
server {
  listen 80;
  listen 443 ssl;
  listen [::]:443 ssl;
  ssl_certificate     /var/www/html/members/$member/ssl.pem;
  ssl_certificate_key /var/www/html/members/$member/ssl.key;
  ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
  ssl_ciphers         HIGH:!aNULL:!MD5;
  server_name core.$member dev.$member;
  location / {
    proxy_pass http://backend_dwl_php:$port;
    proxy_http_version 1.1;
    proxy_redirect     off;
    proxy_set_header   Upgrade \$http_upgrade;
    proxy_set_header   Connection \"Upgrade\";
    proxy_set_header   Host \$host;
    proxy_set_header   X-Real-IP \$remote_addr;
    proxy_set_header   X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header   X-Forwarded-Host \$server_name;
  }
}
"
  fi;
done

FILE="default-prod.conf"
/bin/cat <<EOM >$FILE
$nginxconf
EOM
 
port=8800 
for file in members/*; do
  if [[ -d "$file" && ! -L "$file" ]]; then
    newport=$((port++))
    dirname=$file
    member="${dirname%"${dirname##*[!/]}"}"
    member="${member##*/}"  

    export PORT=$port
    export MEMBER=$member 
    export MEMBERSLUG=${member//./}
    docker-compose -p $member up --build -d  
  fi;
done