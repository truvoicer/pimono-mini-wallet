# Technical Assignment: "Mini Wallet" Application


### Setup Guide
1. Clone repository
```
git clone https://github.com/truvoicer/pimono-mini-wallet.git
```
2. Change directory
```
cd pimono-mini-wallet
```
4. Copy .env
```
cp .env-example .env
```
5. Fill in .env vars (PUSHER, DB, APP_URL etc.)

6. Install dependencies
```
composer install
npm install
```

7. Migrate and seed db
```
php artisan migrate:fresh
php artisan db:seed
```

8. Build
```
npm run build
```

9. Cache routes/config and start queue worker
```
php artisan config:clear
php artisan route:cache
php artisan queue:work
```

10. If you have Laravel Herd installed you should be able to go to the site url, if not then run:
```
php artisan serve
```

12. Navigate to the homepage and you should be redirected to the login page

11. You can run tests with
```
php artisan test
```


