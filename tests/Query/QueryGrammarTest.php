<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Query\Grammar;
use Tamayo\Stretchy\Query\Builder;
use Tamayo\Stretchy\Query\Processor;
use Tamayo\Stretchy\Query\Clause\Factory;

class QueryGrammarTest extends PHPUnit_Framework_TestCase
{

	public function testSingleMatch()
	{
		$builder = $this->getBuilder();

		$builder->match('foo', 'bar', function($match)
		{
			$match->operator('and');
			$match->zeroTermsQuery('all');
			$match->cutoffFrequency(0.001);
			$match->lenient(true);
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":{"operator":"and","zero_terms_query":"all","cutoff_frequency":0.001,"lenient":true,"query":"bar","type":"boolean"}}}}}', $json);
	}

	public function testSingleMatchPhrase()
	{
		$builder = $this->getBuilder();

		$builder->matchPhrase('foo', 'bar');

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":{"query":"bar","type":"phrase"}}}}}', $json);
	}

	public function testSingleMatchPhrasePrefix()
	{
		$builder = $this->getBuilder();

		$builder->matchPhrasePrefix('foo', 'bar');

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":{"query":"bar","type":"phrase_prefix"}}}}}', $json);
	}

	public function testSingleMultiMatch()
	{
		$builder = $this->getBuilder();

		$builder->multiMatch(['foo', 'bar'], 'baz');

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"multi_match":{"fields":["foo","bar"],"query":"baz","type":"best_fields"}}}}', $json);
	}

	public function testSingleBoolWithNestedMatch()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->match('foo', 'bar');
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
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"should":[{"match":{"foo":{"query":"bah","type":"boolean"}}},{"match":{"foo":{"query":"qux","type":"boolean"}}}],"must_not":{"match":{"foo":{"query":"baz","type":"boolean"}}},"must":{"match":{"foo":{"query":"bar","type":"boolean"}}},"minimum_should_match":1}}}}', $json);
	}

	public function testNestedBool()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->match('foo', 'bar');

				$must->bool(function($subQuery)
				{
					$subQuery->must(function($must)
					{
						$must->match('foo', 'baz');
					});

					$subQuery->boost(1.0);
				});
			});

			$query->minimumShouldMatch(1);
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"match":{"foo":{"query":"bar","type":"boolean"}}},{"bool":{"must":{"match":{"foo":{"query":"baz","type":"boolean"}}},"boost":1}}],"minimum_should_match":1}}}}', $json);
	}

	public function testSingleBoosting()
	{
		$builder = $this->getBuilder();

		$builder->boosting(function($query)
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
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"boosting":{"negative":{"match":{"bar":{"query":"bah","type":"boolean"}}},"positive":{"match":{"bar":{"query":"baz","type":"boolean"}}},"negative_boost":0.2}}}}', $json);
	}

	public function testNestedBoosting()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->boosting(function($query)
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
				});
			});

		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"boosting":{"negative":{"match":{"bar":{"query":"bah","type":"boolean"}}},"positive":{"match":{"bar":{"query":"baz","type":"boolean"}}},"negative_boost":0.2}}}}}}',$json);
	}

	public function testSingleCommonTerms()
	{
		$builder = $this->getBuilder();

		$builder->common('foo', 'bar', function($query)
		{
			$query->cutoffFrequency(0.001);

			$query->minimumShouldMatch(function($minimumShouldMatch)
			{
				$minimumShouldMatch->lowFreq(2);
				$minimumShouldMatch->highFreq(3);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"common":{"foo":{"minimum_should_match":{"low_freq":2,"high_freq":3},"cutoff_frequency":0.001,"query":"bar"}}}}}', $json);
	}

	public function testNestedCommonTerms()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->common('foo', 'bar', function($query)
				{
					$query->cutoffFrequency(0.001);

					$query->minimumShouldMatch(function($minimumShouldMatch)
					{
						$minimumShouldMatch->lowFreq(2);
						$minimumShouldMatch->highFreq(3);
					});
				});
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"common":{"foo":{"minimum_should_match":{"low_freq":2,"high_freq":3},"cutoff_frequency":0.001,"query":"bar"}}}}}}}', $json);
	}

	public function testSingleGeoShape()
	{
		$builder = $this->getBuilder();

		$builder->geoShape('location', [[13, 53],[14, 52]]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"geo_shape":{"location":{"shape":{"coordinates":[[13,53],[14,52]],"type":"envelope"}}}}}}', $json);
	}

	public function testSingleGeoShapeIndexedShape()
	{
		$builder = $this->getBuilder();

		$builder->geoShape('location', [], 'indexed_shape', ['id' => 'DEU', 'type'=> 'countries', 'index'=> 'shapes', 'path'=> 'location']);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"geo_shape":{"location":{"indexed_shape":{"id":"DEU","type":"countries","index":"shapes","path":"location"}}}}}}', $json);
	}

	public function testNestedGeoShape()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->geoShape('location', [[13, 53],[14, 52]]);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"geo_shape":{"location":{"shape":{"coordinates":[[13,53],[14,52]],"type":"envelope"}}}}}}}}', $json);
	}

	public function testSingleTerm()
	{
		$builder = $this->getBuilder();

		$builder->term('foo', 'bar', function($term)
		{
			$term->boost(2);
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"term":{"foo":{"boost":2,"value":"bar"}}}}}', $json);
	}

	public function testNestedTerm()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->term('foo', 'bar', ['boost' => 2]);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"term":{"foo":{"boost":2,"value":"bar"}}}}}}}', $json);
	}

	public function testConstantScore()
	{
		$builder = $this->getBuilder();

		$builder->constantScore(function($constantScore)
		{
			$constantScore->filter(function($filter)
			{
				$filter->term('foo', 'bar');
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"constant_score":{"filter":{"term":{"foo":{"value":"bar"}}}}}}}', $json);
	}

	public function testDisMax()
	{
		$builder = $this->getBuilder();

		$builder->disMax(function($disMax)
		{
			$disMax->tieBreaker(0.7);
			$disMax->boost(1.2);

			$disMax->queries(function($queries)
			{
				$queries->term('age', 34);
				$queries->term('age', 35);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"dis_max":{"queries":[{"term":{"age":{"value":34}}},{"term":{"age":{"value":35}}}],"tie_breaker":0.7,"boost":1.2}}}}', $json);
	}

	public function testNestedDisMax()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->disMax(function($disMax)
				{
					$disMax->tieBreaker(0.7);
					$disMax->boost(1.2);

					$disMax->queries(function($queries)
					{
						$queries->term('age', 34);
						$queries->term('age', 35);
					});
				});
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"dis_max":{"queries":[{"term":{"age":{"value":34}}},{"term":{"age":{"value":35}}}],"tie_breaker":0.7,"boost":1.2}}}}}}', $json);
	}

	public function testSingleFiltered()
	{
		$builder = $this->getBuilder();

		$builder->filtered(function($filtered)
		{
			$filtered->query(function($query)
			{
				$query->match('bar', 'baz');
			});

			$filtered->filter(function($filter)
			{
				$filter->range('created', ['gte' => 'now - 1d / d']);
			});

		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"filtered":{"filter":{"range":{"created":{"gte":"now - 1d \/ d"}}},"query":{"match":{"bar":{"query":"baz","type":"boolean"}}}}}}}', $json);
	}

	public function testNestedFiltered()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->filtered(function($filtered)
				{
					$filtered->query(function($query)
					{
						$query->match('bar', 'baz');
					});

					$filtered->filter(function($filter)
					{
						$filter->range('created', ['gte' => 'now - 1d / d']);
					});

				});
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"filtered":{"filter":{"range":{"created":{"gte":"now - 1d \/ d"}}},"query":{"match":{"bar":{"query":"baz","type":"boolean"}}}}}}}}}', $json);
	}

	public function testSingleRange()
	{
		$builder = $this->getBuilder();

		$builder->range('created', ['gte' => 'now - 1d / d']);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"range":{"created":{"gte":"now - 1d \/ d"}}}}}', $json);
	}

	public function testNestedRange()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->range('created', ['gte' => 'now - 1d / d']);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"range":{"created":{"gte":"now - 1d \/ d"}}}}}}}', $json);
	}

	public function testSingleFuzzyLikeThis()
	{
		$builder = $this->getBuilder();

		$builder->fuzzyLikeThis(['bar', 'baz'], 'text like this one', ['fuzziness' => 1.5]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"fuzzy_like_this":{"fuzziness":1.5,"like_text":"text like this one","fields":["bar","baz"]}}}}', $json);
	}

	public function testNestedFuzzyLikeThis()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->fuzzyLikeThis(['bar', 'baz'], 'text like this one', ['fuzziness' => 1.5]);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"fuzzy_like_this":{"fuzziness":1.5,"like_text":"text like this one","fields":["bar","baz"]}}}}}}', $json);
	}

	public function testSingleFuzzyLikeThisField()
	{
		$builder = $this->getBuilder();

		$builder->fuzzyLikeThisField('baz', 'text like this one', ['fuzziness' => 1.5]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"fuzzy_like_this_field":{"baz":{"fuzziness":1.5,"like_text":"text like this one"}}}}}', $json);
	}

	public function testNestedFuzzyLikeThisField()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->fuzzyLikeThisField('baz', 'text like this one', ['fuzziness' => 1.5]);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"fuzzy_like_this_field":{"baz":{"fuzziness":1.5,"like_text":"text like this one"}}}}}}}', $json);
	}

	public function testSingleFuzzy()
	{
		$builder = $this->getBuilder();

		$builder->fuzzy('price', 12, ['fuzziness' => 2]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"fuzzy":{"price":{"fuzziness":2,"value":12}}}}}', $json);
	}

	public function testNestedFuzzy()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->fuzzy('price', 12, ['fuzziness' => 2]);
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"fuzzy":{"price":{"fuzziness":2,"value":12}}}}}}}', $json);
	}

	public function testHasChild()
	{
		$builder = $this->getBuilder();

		$builder->hasChild(function($hasChild)
		{
			$hasChild->type('blog_tag');
			$hasChild->scoreMode('sum');

			$hasChild->minChildren(2);
			$hasChild->maxChildren(10);

			$hasChild->query(function($query)
			{
				$query->term('bar', 'baz');
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"has_child":{"query":{"term":{"bar":{"value":"baz"}}},"type":"blog_tag","score_mode":"sum","min_children":2,"max_children":10}}}}', $json);
	}

	public function testNestHasChild()
	{
		$builder = $this->getBuilder();

		$builder->bool(function($query)
		{
			$query->must(function($must)
			{
				$must->hasChild(function($hasChild)
				{
					$hasChild->type('blog_tag');
					$hasChild->scoreMode('sum');

					$hasChild->minChildren(2);
					$hasChild->maxChildren(10);

					$hasChild->query(function($query)
					{
						$query->term('bar', 'baz');
					});
				});
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"has_child":{"query":{"term":{"bar":{"value":"baz"}}},"type":"blog_tag","score_mode":"sum","min_children":2,"max_children":10}}}}}}', $json);
	}

	public function testHasParent()
	{
		$builder = $this->getBuilder();

		$builder->hasParent(function($hasParent)
		{
			$hasParent->type('blog');
			$hasParent->scoreMode('score');

			$hasParent->query(function($query)
			{
				$query->term('bar', 'baz');
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"has_parent":{"query":{"term":{"bar":{"value":"baz"}}},"type":"blog","score_mode":"score"}}}}', $json);
	}

	public function testIds()
	{
		$builder = $this->getBuilder();

		$builder->ids([2, 100], 'my_type');

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"ids":{"values":[2,100],"type":"my_type"}}}}', $json);
	}

	public function testIndices()
	{
		$builder = $this->getBuilder();

		$builder->indices(['index1', 'index2'], function($indices)
		{
			$indices->query(function($query)
			{
				$query->term('tag', 'wow');
			});

			$indices->noMatchQuery(function($noMatchQuery)
			{
				$noMatchQuery->term('tag', 'kow');
			});
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"indices":{"no_match_query":{"term":{"tag":{"value":"kow"}}},"query":{"term":{"tag":{"value":"wow"}}}}}}}', $json);
	}

	public function testMatchAll()
	{
		$builder = $this->getBuilder();

		$builder->matchAll();

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match_all":{}}}}', $json);

		$builder = $builder->newInstance();

		$builder->matchAll(['boost' => 1.2]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match_all":{"boost":1.2}}}}', $json);
	}

	public function testMoreLikeThis()
	{
		$builder = $this->getBuilder();

		$builder->moreLikeThis(['name.first', 'name.last'], 'text like this one', ['min_term_freq' => 1, 'max_query_terms' => 12]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"more_like_this":{"min_term_freq":1,"max_query_terms":12,"fields":["name.first","name.last"],"like_text":"text like this one"}}}}', $json);

		$builder = $builder->newInstance();

		$builder->moreLikeThis(['name.first', 'name.last'], 'text like this one', function($moreLikeThis)
		{
			$moreLikeThis->minTermFreq(1);
			$moreLikeThis->maxQueryTerms(12);

			$moreLikeThis->docs([
				['_index' => 'test', '_type' => 'type', '_id' => 1],
				['_index' => 'test', '_type' => 'type', '_id' => 2]
			]);

			$moreLikeThis->ids(['3', '4']);
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"more_like_this":{"min_term_freq":1,"max_query_terms":12,"docs":[{"_index":"test","_type":"type","_id":1},{"_index":"test","_type":"type","_id":2}],"ids":["3","4"],"fields":["name.first","name.last"],"like_text":"text like this one"}}}}', $json);
	}

	public function testNested()
	{
		$builder = $this->getBuilder();

		$builder->nested(function($nested)
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
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"nested":{"query":{"bool":{"must":[{"match":{"obj1.name":{"query":"blue","type":"boolean"}}},{"range":{"obj1.count":{"gt":5}}}]}},"path":"obj1","score_mode":"avg"}}}}', $json);
	}

	public function testPrefix()
	{
		$builder = $this->getBuilder();

		$builder->prefix('user', 'ki', ['boost' => 2]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"prefix":{"user":{"boost":2,"value":"ki"}}}}}', $json);
	}

	public function testQueryString()
	{
		$builder = $this->getBuilder();

		$builder->queryString('this AND that OR thus', ['default_field' => 'content']);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"query_string":{"default_field":"content","query":"this AND that OR thus"}}}}', $json);
	}

	public function testSimpleQueryString()
	{
		$builder = $this->getBuilder();

		$builder->simpleQueryString('"fried eggs" +(eggplant | potato) -frittata', ['analyzer' => 'snowball']);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"simple_query_string":{"analyzer":"snowball","query":"\"fried eggs\" +(eggplant | potato) -frittata"}}}}', $json);
	}

	public function testRegex()
	{
		$builder = $this->getBuilder();

		$builder->regex('name.first', 's.*y', ['boost' => 1.2]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"regex":{"name.first":{"boost":1.2,"value":"s.*y"}}}}}', $json);
	}

	public function testTerms()
	{
		$builder = $this->getBuilder();

		$builder->terms('tags', ['blue', 'pill'], ['minimum_should_match' => 1]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"terms":{"minimum_should_match":1,"tags":["blue","pill"]}}}}', $json);
	}

	public function testWildcardArray()
	{
		$builder = $this->getBuilder();

		$builder->raw(['query' => ['match' => ['testField' => 'abc']]]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match":{"testField":"abc"}}}}', $json);
	}

	public function testWildcardJson()
	{
		$builder = $this->getBuilder();

		$builder->raw('{"query":{"match":{"testField":"abc"}}}');

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"match":{"testField":"abc"}}}}', $json);
	}

	public function testRaw()
	{
		$builder = $this->getBuilder();

		$builder->wildcard('user', 'ki*y', ['boost' => 2.0]);

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"wildcard":{"user":{"boost":2,"value":"ki*y"}}}}}', $json);
	}

	public function getGrammar()
	{
		return new Grammar;
	}

	public function getConnection()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');

		$connection->shouldReceive('getIndexPrefix')->andReturn('');

		return $connection;
	}

	public function getProcessor()
	{
		return new Processor;
	}

	public function getClauseFactory()
	{
		return new Factory;
	}

	public function getBuilder()
	{
		return new Builder($this->getConnection(), $this->getGrammar(), $this->getProcessor(), $this->getClauseFactory());
	}
}
