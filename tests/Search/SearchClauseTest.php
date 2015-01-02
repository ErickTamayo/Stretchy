<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Processor;
use Tamayo\Stretchy\Search\Clauses\Clause;

class SearchClauseTest extends PHPUnit_Framework_TestCase
{
	public function testClause()
	{
		$clause = new Clause($this->getBuilder());

		$clause->setConstraints(['foo_bar']);

		$clause->fooBar('baz');

		$this->assertEquals(['foo_bar' => 'baz'], $clause->getAffectedConstraints());
	}

    /**
     * @expectedException InvalidArgumentException
     */
	public function testClauseException()
	{
		$clause = new Clause($this->getBuilder());

		$clause->fooBar('baz');
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
