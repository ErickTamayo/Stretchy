#Introduction

Here you will find the Query API for search in elasticsearch.

# Match Query

For **match** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-query.html)

```php
Stretchy::search('foo')->match('bar', 'Stretchy')->get();
```

To provide additional parameters simply pass as an array as third argument:

```php
Stretchy::search('foo')->match('bar', 'baz', ['operator' => 'and', 'zero_terms_query' => 'all'])
	->get();
```

###phrase

```php
Stretchy::search('foo')->matchPhrase('bar', 'baz')->get();
```
###match_phrase_prefix

```php
Stretchy::search('foo')->matchPhrasePrefix('bar', 'baz')->get();
```

# Multi Match Query

For **multi match** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html)

```php
Stretchy::search('foo')->multiMatch(['bar', 'baz'], 'bah')->get();
```

To provide additional parameters:

```php
Stretchy::search('foo')
	->multiMatch(['bar', 'baz'], 'bah', ['tie_breaker' => 0.3, 'type' => 'most_fields'])
	->get();
```


# Bool query

For **bool** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)

For easy querying you can query specify

```php
Stretchy::search('foo')
	->bool(function($query)
	{
		$query->must(function($must)
		{
			$must->match('foo', 'bar');
		});

		$query->filter(function($filter) {
			$filter->term('foo', 'abc');
		});

		$query->mustNot(function($mustNot)
		{
			$mustNot->match('foo', 'baz');
		});

		$query->should(function($should)
		{
			$should->match('foo', 'bah');
			$should->match('foo', 'qux');
		});

		$query->minimumShouldMatch(1);
		$query->boost(3);
	})
	->get();
```
or

```php
Stretchy::search('foo')
	->bool(function($query)
	{
		$query->must('match', 'foo', 'bar')
		    ->filter('term', 'foo', 'qux')
			->mustNot('match', 'foo', 'baz')
			->should('match', 'foo', 'bah', ['zero_terms_query' => 'all'])
			->minimumShouldMatch(2)
			->boost(4);
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
		->common('bar', 'The brown fox', ['cutoff_frequency' => 0.001, 'minimum_should_match' => ['low_freq' => 2, 'high_freq' => 3]])
		->get();
```

# Constant score query

For **constant score** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-constant-score-query.html#query-dsl-constant-score-query)

```php
Stretchy::search('foo')
	->constantScore(function($constantScore)
	{
		$constantScore->filter(function($filter)
		{
			$filter->term('bar', 'baz');
		});
	})
	->get();
```

# Dis max query

For **dis max** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-dis-max-query.html)

```php
Stretchy::search('foo')
	->disMax(function($disMax)
	{
		$disMax->tieBreaker(0.7);
		$disMax->boost(1.2);

		$disMax->queries(function($queries)
		{
			$queries->term('age', 34);
			$queries->term('age', 35);
		});
	})
	->get();
```

also, as the same as elasticsearch, you can use the alias **fltField**:

```php
Stretchy::search('foo')->fltField('baz', 'text like this one', ['fuzziness' => 1.5])->get();
```

# Fuzzy query

For **fuzzy** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html)

```php
Stretchy::search('foo')->fuzzy('price', 12, ['fuzziness' => 2])->get();
```

or

```php
Stretchy::search('foo')
	->fuzzy('price', 12, function($fuzzy)
	{
		$fuzzy->fuzziness(2);
	})
	->get();
```

# Geoshape query

For **geoshape** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html)

```php
Stretchy::search('foo')->geoShape('location', 13, 53, 14, 52)->get();
```

###Indexed shape

```php
Stretchy::search('foo')->preindexedGeoShape('location', 'DEU', 'countries', 'shapes', 'location')->get();
```

# Has child query

For **has child** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-has-child-query.html)

```php
Stretchy::search('foo')->hasChild('blog_tag', 'term', 'tag', 'something', ['boost' => 2.0])->get();
```
or
```php
Stretchy::search('foo')->
	->hasChild('blog_tag', function ($query) {
		$query->scoreMode('sum');
		$query->minChildren(2);
		$query->maxChildren(10);
		$query->query('term', 'tag', 'something');
	})
	->get();
```

# Has parent query

For **has parent** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-has-parent-query.html)

```php
Stretchy::search('foo')->hasParent('blog', 'term', 'tag', 'something')->get();
```
or
```php
Stretchy::search('foo')->
	->hasParent('blog_tag', function ($query) {
		$query->scoreMode('score');
		$query->query('term', 'tag', 'something');
	})
	->get();
```

# Ids query

For **ids** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-ids-query.html)

```php
Stretchy::search('foo')->ids([2, 100], 'my_type')->get();
```

# Indices query

For **indices** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-indices-query.html)

```php
Stretchy::search('foo')
	->indices(['index1', 'index2'], function($indices)
	{
		$indices->query(function($query)
		{
			$query->term('tag', 'wow');
		});

		$indices->noMatchQuery(function($noMatchQuery)
		{
			$noMatchQuery->term('tag', 'kow');
		});
	})
	->get();
```

# Match all query

For **match all** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html)

```php
Stretchy::search('foo')->matchAll()->get();
```

or

```php
Stretchy::search('foo')->matchAll(['boost' => 1.2])->get();
```

# More like this query

For **match all** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html)

```php
Stretchy::search('foo')
	->moreLikeThis(['title', 'description'], 'Once upon a time', ['min_term_freq' => 1, 'max_query_terms' => 12])
	->get();
```

or

```php
Stretchy::search('foo')
	->moreLikeThis(['name.first', 'name.last'], function($query) {
		$query->like('imdb', 'movies', '1');
		$query->like('imdb', 'movies', '2');
		$query->like('and potentially some more text here as well');
		$query->minTermFreq(1);
		$query->maxQueryTerms(12);
	})
	->get();
```

or

```php
Stretchy::search('foo')
	->moreLikeThis(['name.first', 'name.last'], function($query) {
		$query->like('marvel', 'quotes', [
			'name' => [
				'first' => 'Ben',
				'last' => 'Grim'
			],
			'tweet' => 'You got no idea what I\'d... what I\'d give to be invisible.'
		]);
		$query->like('marvel', 'quotes', '2');
		$query->minTermFreq(1);
		$query->maxQueryTerms(12);
	})
	->get();
```

# Nested query

For **nested** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html)

```php
Stretchy::search('foo')
	->nested(function($nested)
	{
		$nested->path('obj1');
		$nested->scoreMode('avg');

		$nested->query(function($query)
		{
			$query->bool(function($bool)
			{
				$bool->must(function($must)
				{
					$must->match('obj1.name', 'blue');
					$must->range('obj1.count', ['gt' => 5]);
				});
			});
		});
	})
	->get();
```

# Prefix query

For **prefix** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html)

```php
Stretchy::search('foo')->prefix('user', 'ki', ['boost' => 2])->get();
```

# Query string query

For **query string** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html)

```php
Stretchy::search('foo')->queryString('this AND that OR thus', ['default_field' => 'content'])->get();
```

# Regex query

For **query string** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html)

```php
Stretchy::search('foo')->regex('name.first', 's.*y', ['boost' => 1.2])->get();
```

# Range query

For **range** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-range-query.html)

```php
Stretchy::search('foo')->range('created', ['gte' => 'now - 1d / d'])->get();
```

or

```php
Stretchy::search('foo')
	->range('created', function($range)
	{
		$range->gte('now - 1d/ d');
	})
	->get();
```


# Term query

For **term** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-term-query.html)

```php
Stretchy::search('foo')->term('bar', 'baz')->get();
```

To provide additional parameters:

```php
Stretchy::search('foo')
	->term('bar', 'baz', function($term)
	{
		$term->boost(2);
	})
	->get();
```

or

```php
Stretchy::search('foo')->term('bar', 'baz', ['boost' => 2])->get();
```

# Terms query

For **terms** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html)

```php
Stretchy::search('foo')->terms('tags', ['blue', 'pill'], ['minimum_should_match' => 1])->get();
```

# Wildcard query

For **terms** reference in elasticsearch [click here](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html)

```php
Stretchy::search('foo')->wildcard('user', 'ki*y', ['boost' => 2.0])->get();
```

# Raw query

You also can do a raw query by a json or array

```php
Stretchy::search('foo')->raw('{"query":{"match":{"testField":"abc"}}}')->get();
```

or

```php
Stretchy::search('foo')->raw(['query' => ['match' => ['testField' => 'abc']]])->get();
```
