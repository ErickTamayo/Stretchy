#Introduction

Here you will find the Query API for search in elasticsearch.

# Match Query

For **match** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-query.html)

```php
Stretchy::search('foo')->match('bar', 'Stretchy')->get();
```

To provide additional parameters:

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
Stretchy::search('foo')
	->multiMatch(['bar', 'baz'], 'bah', function($match)
	{
		$match->tieBreaker(0.3);
		$match->type('most_fields');
	})
	->get();
```

# Bool query

For **bool** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)

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

# Boosting query

For **boosting** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html)

```php
Stretchy::search('foo')
	->boosting(function($query)
	{
		$query->positive(function($positive)
		{
			$positive->match('bar', 'baz');
		});

		$query->negative(function($negative)
		{
			$negative->match('bar', 'bah');
		});

		$query->negativeBoost(0.2);
	})
	->get();
```

# Common terms query

For **common terms** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html)

```php
Stretchy::search('foo')
		->common('bar', 'The brown fox', function($query)
		{
			$query->cutoffFrequency(0.001);

			$query->minimumShouldMatch(function($minimumShouldMatch)
			{
				$minimumShouldMatch->lowFreq(2);
				$minimumShouldMatch->highFreq(3);
			});
		})
		->get();
```
