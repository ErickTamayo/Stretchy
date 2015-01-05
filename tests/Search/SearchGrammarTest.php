<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Processor;

class SearchGrammarTest extends PHPUnit_Framework_TestCase
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
			});

			$query->minimumShouldMatch(1);
		});

		$json = $builder->toJson();

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"should":[{"match":{"foo":{"query":"bah","type":"boolean"}}}],"must_not":[{"match":{"foo":{"query":"baz","type":"boolean"}}}],"must":[{"match":{"foo":{"query":"bar","type":"boolean"}}}],"minimum_should_match":1}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"match":{"foo":{"query":"bar","type":"boolean"}}},{"bool":{"must":[{"match":{"foo":{"query":"baz","type":"boolean"}}}],"boost":1}}],"minimum_should_match":1}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"boosting":{"negative":[{"match":{"bar":{"query":"bah","type":"boolean"}}}],"positive":[{"match":{"bar":{"query":"baz","type":"boolean"}}}],"negative_boost":0.2}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"boosting":{"negative":[{"match":{"bar":{"query":"bah","type":"boolean"}}}],"positive":[{"match":{"bar":{"query":"baz","type":"boolean"}}}],"negative_boost":0.2}}]}}}}',$json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"common":{"foo":{"minimum_should_match":{"low_freq":2,"high_freq":3},"cutoff_frequency":0.001,"query":"bar"}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"geo_shape":{"location":{"shape":{"coordinates":[[13,53],[14,52]],"type":"envelope"}}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"term":{"foo":{"boost":2,"value":"bar"}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"constant_score":{"filter":[{"term":{"foo":{"value":"bar"}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"dis_max":{"queries":[{"term":{"age":{"value":34}}},{"term":{"age":{"value":35}}}],"tie_breaker":0.7,"boost":1.2}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"filtered":{"filter":[{"range":{"created":{"gte":"now - 1d \/ d"}}}],"query":[{"match":{"bar":{"query":"baz","type":"boolean"}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"filtered":{"filter":[{"range":{"created":{"gte":"now - 1d \/ d"}}}],"query":[{"match":{"bar":{"query":"baz","type":"boolean"}}}]}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"range":{"created":{"gte":"now - 1d \/ d"}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"fuzzy_like_this":{"fuzziness":1.5,"like_text":"text like this one","fields":["bar","baz"]}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"fuzzy_like_this_field":{"baz":{"fuzziness":1.5,"like_text":"text like this one"}}}]}}}}', $json);
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

		$this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":[{"fuzzy":{"price":{"fuzziness":2,"value":12}}}]}}}}', $json);
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

	public function getBuilder()
	{
		return new Builder($this->getConnection(), $this->getGrammar(), $this->getProcessor());
	}
}
