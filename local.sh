#!/bin/bash
   
export COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1 
export DOCKERHOST=$(docker run --rm alpine ip route | awk 'NR==1 {print $3}')
export PROJECT=${PWD}
export MEMBERENV="local"

if [[ "$1" != "" ]]
then 
  port=$1
else
  read -p "ENTER PORT :" port
fi

if [[ "$1" != "" ]]
then 
  member=$1
else
  read -p "MEMBER DOMAIN :" member
fi

export PORT=$port
export MEMBER=$member 
export MEMBERSLUG=${member//./}

PROJECTDIRNAME=${PWD##*/}

echo "server {
    listen $PORT; 
    server_name _;
    root /var/www/html/public;
    index index.php index.html;
    location / {
        try_files \$uri \$uri/ /index.php?q=\$uri&\$args;
    }
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass backend_dwl_php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }
}
" > 'default-local.conf' 

docker ps -a | grep Exit | cut -d ' ' -f 1 | xargs docker rm
docker-compose -p $MEMBERSLUG up --build -d  

docker ps 