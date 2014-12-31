#Introduction

Here you will find the Query API for search in elasticsearch.

# Match Query

For **match** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-query.html)

```php
Stretchy::search('foo')->match('bar', 'Stretchy')->get();
```

To provide additional parameters:

```php
Stretchy::search('foo')->match('bar', 'Stretchy', function($match)
		{
			$match->operator('and');
			$match->zeroTermsQuery('all');
			$match->cutoffFrequency(0.001);
		})->get();
```

###phrase

```php
Stretchy::search('foo')->matchPhrase('bar', 'Stretchy')->get();
```
###match_phrase_prefix

```php
Stretchy::search('foo')->matchPhrasePrefix('bar', 'Stretchy')->get();
```

# Multi Match Query

For **multi match** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html)

```php
Stretchy::search('foo')->multiMatch(['bar', 'baz'], 'bah')->get();
```

To provide additional parameters:

```php
Stretchy::search('foo')->multiMatch(['bar', 'baz'], 'bah', function($match)
			{
				$match->tieBreaker(0.3);
				$match->type('most_fields');
			})->get();
```
