Getting Started
=========

Installation
----

1. In your **composer.json**, add the dependency: `"tamayo/stretchy": "dev-master"`

2. Add the Stretchy service provider in your app.config:
```php
        'Tamayo\Stretchy\StretchyServiceProvider'
```

3. Add the following aliases:
```php
		'Index'    => 'Tamayo\Stretchy\Facades\Index',
		'Stretchy' => 'Tamayo\Stretchy\Facades\Stretchy'
```

4. (Optional) If you want to override the default configuration:
```sh
php artisan config:publish tamayo/stretchy
```
Located in your laravel config directory: **packages/tamayo/stretchy/config.php**

Quick Examples
----

####Create Index
To create a basic index just do the following:
```php
Index::create('foo');
```
If you want to specify shards and replicas:
```php
Index::create('foo', function($index)
	{
		$index->shards(5);
		$index->replicas(1);
	});
```
####Delete Index
```php
Index::delete('foo');
```
####Document Indexing*
```php
Index::index('foo')
    ->type('tweet')
    ->insert([
        'username' => '@ericktamayo',
        'tweet'    => 'Hello world!'
    ]);
```
*Subject to change soon

###Searching

#####Match Query
```php
Stretchy::search('foo')->match('bar', 'Stretchy')->get();
```
To provide additional parameters:
```php
Stretchy::search('foo')
	->match('bar', 'baz', ['operator' => 'and', 'zero_terms_query' => 'all'])
	->get();
```
or
```php
Stretchy::search('foo')
	->match('bar', 'Stretchy', function($match)
	{
		$match->operator('and');
		$match->zeroTermsQuery('all');
		$match->cutoffFrequency(0.001);
	})
	->get();
```

#####Term Query
```php
Stretchy::search('foo')->term('bar', 'baz')->get();
```

To provide additional parameters:
```php
Stretchy::search('foo')->term('bar', 'baz', ['boost' => 2])->get();
```
or
```php
Stretchy::search('foo')
	->term('bar', 'baz', function($term)
	{
		$term->boost(2);
	})
	->get();
```

#####Bool Query
```php
Stretchy::search('foo')
	->bool(function($query)
	{
		$query->must(function($must)
		{
			$must->match('bar', 'baz');
		});

		$query->mustNot(function($mustNot)
		{
			$mustNot->match('bar', 'qux');
		});

		$query->should(function($should)
		{
			$should->match('bar', 'bah');
		});

		$query->minimumShouldMatch(1);
	})
	->get();
```
