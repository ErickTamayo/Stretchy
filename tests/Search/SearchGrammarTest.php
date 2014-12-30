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
