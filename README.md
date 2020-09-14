<p> We are using passport package for authentication</p>
composer install <br />
composer update <br /> 
copy your .env.example and rename to .env <br />
php artisan migrate <br />
php artisan passport:install <br />
php artisan passport:client --persona <br />
php artisan key:generate <br />
composer dump-autoload <br />
php artisan config:cache <br />
php artisan cache:clear