# Technical Assignment: "Mini Wallet" Application

### Brief

The app has a transactions page and a transfer page.

On the bottom of the sidebar you can change user settings such as profile, currency, theme, password etc.

I added roles for the users, only admins and superusers can change some of the settings such as currency.

I also added a policy for changing settings (SettingPolicy)

I added phpstan for static analysis as well.

### Setup Guide
1. Clone repository
```
git clone https://github.com/truvoicer/pimono-mini-wallet.git
```
2. Change directory
```
cd pimono-mini-wallet
```

6. Install dependencies
```
composer install
npm install
```

7. Copy .env
```
cp .env.example .env
```
8. Fill in .env vars (PUSHER, DB, APP_URL etc.)

9. Copy .env and generate key
```
php artisan key:generate
```

10. Migrate and seed db
```
php artisan migrate:fresh
php artisan db:seed
```

11. Build
```
npm run build
```

12. Cache routes/config and start queue worker
```
php artisan config:clear
php artisan route:cache
php artisan queue:work
```

13. If you have Laravel Herd installed you should be able to go to the site url, if not then run:
```
php artisan serve
```

14. Navigate to the homepage and you should be redirected to the login page

15. You can run tests with
```
php artisan test
```
16. You can run phpstan with
```
./vendor/bin/phpstan analyse
```


