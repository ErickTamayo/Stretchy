<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Processor;

class SearchGrammarTest extends PHPUnit_Framework_TestCase
{

	public function testMatch()
	{
		$builder = $this->getBuilder();

		$builder->match('foo', function($match)
		{
			$match->query('bar');
			$match->operator('and');
			$match->zeroTermsQuery('all');
			$match->cutoffFrequency(0.001);

		});

		$json = $builder->toJson();

		dd($json);

		$this->assertEquals('', $json);

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
