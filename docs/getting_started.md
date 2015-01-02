Getting Started
=========

Installation
----

1. In your **composer.json**, add the dependency: `"tamayo/stretchy": "dev-master"`

2. Add the Stretchy service provider in your app.config:
```php
		'Index'		=> 'Tamayo\Stretchy\Facades\Index',
		'Stretchy'	=> 'Tamayo\Stretchy\Facades\Stretchy'
```

3. Add the following aliases:

    `'Index' => 'Tamayo\Stretchy\Facades\Index'`

4. (Optional) If you want to override the default configuration:
```sh
php artisan config:publish tamayo/stretchy
```
In your laravel config directory: **packages/tamayo/stretchy/config.php**

Quick Examples
----
