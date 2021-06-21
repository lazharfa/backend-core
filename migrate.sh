#!/bin/bash
php artisan migrate --env=bantutetangga-prod
php artisan migrate --env=insanbumimandiri-prod
php artisan migrate --env=pesantrenquran-prod
php artisan migrate --env=rumahasuh-prod
php artisan migrate --env=rumahpangan-prod
php artisan migrate --env=bersamaquran-prod
php artisan migrate --env=sahabatpedalaman-prod

