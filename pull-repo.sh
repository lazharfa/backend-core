#!/bin/sh
cd "${PWD}"
git reset --hard
git pull
php artisan migrate --env=harapandhuafa-prod
php artisan migrate --env=hatibaik-prod
php artisan migrate --env=insanbumimandiri-prod
php artisan migrate --env=kaunyberbagi-prod
php artisan migrate --env=mitrayatim-prod
php artisan migrate --env=pesantrenquran-prod
php artisan migrate --env=rumahasuh-prod
pm2 restart all
