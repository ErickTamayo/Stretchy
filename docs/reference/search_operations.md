#Introduction

Here you will find the Query API for search in elasticsearch.

# Match Query

For **match** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-query.html)

```php
Stretchy::search('foo')->match('bar', 'Stretchy')->get();
```

To speficy more parameters

```php
Stretchy::search('foo')->match('bar', 'Stretchy', function($match)
		{
			$match->operator('and');
			$match->zeroTermsQuery('all');
			$match->cutoffFrequency(0.001);
		});
```

or

```php
Stretchy::search('foo')->match('bar', function($match)
		{
			$match->query('Stretchy'); //The query must be present when specifying more parameters
			$match->operator('and');
			$match->zeroTermsQuery('all');
			$match->cutoffFrequency(0.001);
		});
```

##phrase

the match phrase is the same as match:

```php
Stretchy::search('foo')->matchPhrase('bar', 'Stretchy')->get();
```
