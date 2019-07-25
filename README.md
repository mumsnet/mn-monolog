# MN Monolog [![Build Status](https://travis-ci.org/mumsnet/mn-monolog.svg?branch=master)](https://travis-ci.org/mumsnet/mn-monolog)

Custom handlers for Monolog

## How to use with Lumen

Add this to your `composer.json` file in the `require` section:

```
"mumsnet/mn-monolog": "^1.0"
```

Run composer to update the packages:

```bash
docker run -it -v `pwd`:/var/www/html mumsnet/php-fpm-lumen:7.3 composer install
```
Add this to your `bootstrap/app.php` file:

```php
if (getenv('PAPERTRAIL_HOSTNAME') !== FALSE) {
    $app->configureMonologUsing(function ($monolog) {
        $papertrailHandler = new PapertrailHandler(
            getenv('PAPERTRAIL_HOSTNAME'),
            getenv('PAPERTRAIL_PORT'),
            getenv('SITE_HOSTNAME'),
            getenv('PAPERTRAIL_PROGRAM_NAME'));
        $monolog->pushHandler($papertrailHandler);
        return $monolog;
    });
}
```