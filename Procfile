web: vendor/bin/heroku-php-apache2 -i .user.ini public
worker: php artisan queue:restart && php artisan queue:work database --queue=default
scheduler: php -d memory_limit=512M artisan schedule:run
