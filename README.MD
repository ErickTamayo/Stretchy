Stretchy
=========
[![Build Status](https://travis-ci.org/ErickTamayo/Stretchy.svg?branch=master)](https://travis-ci.org/ErickTamayo/Stretchy)

Stretchy in an [Elasticsearch] integration for Laravel 5.

Heavily Inspired by Query Builder and Schema of laravel.

Description on going.

### Version
2.0.0 - Alpha

### Documentation

The current documentation is at [stretchy.readthedocs.org](http://stretchy.readthedocs.org/).

#Installation

###Requirements

- PHP 5.5.9+

### Installing with Composer
1. In your **composer.json**, add the dependency: `"tamayo/stretchy": "2.0.0"`

2. Add the Stretchy service provider in your app.config:
```php
        'Tamayo\Stretchy\StretchyServiceProvider'
```

3. Add the following aliases:
```php
		'Index'    => 'Tamayo\Stretchy\Facades\Index',
		'Document' => 'Tamayo\Stretchy\Facades\Document',
		'Stretchy' => 'Tamayo\Stretchy\Facades\Stretchy'
```

4. (Optional) If you want to override the default configuration:
```sh
php artisan config:publish tamayo/stretchy
```
Located in your laravel config directory: **packages/tamayo/stretchy/config.php**

##Quick Examples
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
####Document indexing
```php
Document::index('foo')
    ->type('tweet')
    ->id(13) // Optional (if not specified elastic will generate an unique id)
    ->insert([
        'username' => '@ericktamayo',
        'tweet'    => 'Hello world!'
    ]);
```
####Update a document
```php
Document::index('foo')
    ->type('tweet')
    ->id(13)
    ->update(['tweet' => 'Hello world!!!']);
```
####Get a document
```php
Document::index('foo')->type('tweet')->Id(13)->get();
```

####Delete a document
```php
Document::index('foo')->type('tweet')->Id(13)->delete();
```

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
More examples can be found in the [documentation](http://stretchy.readthedocs.org/).


### Roadmap
- **Documentation**
- **Make the library to be independent from Laravel components**
- **PutMapping API**

###Author
Erick Tamayo - [ericktamayo@gmail.com](mailto:ericktamayo@gmail.com) - [@ericktamayo](http://twitter.com/ericktamayo)

### License
MIT

[ElasticSearch]:http://www.elasticsearch.org/
