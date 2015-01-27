<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Document\Grammar;
use Tamayo\Stretchy\Document\Builder;
use Tamayo\Stretchy\Document\Processor;

class DocumentGrammarTest extends PHPUnit_Framework_TestCase {

	public function testBasicInsert()
	{
		$grammar = $this->getGrammar();

		$builder = $this->getBuilder();

		$builder->index = 'foo';
		$builder->type  = 'bar';
		$builder->id = 1234;

		$payload = ['tamayo' => 'stretchy'];

		$compiled = $grammar->compileInsert($builder, $payload);

		$this->assertEquals($compiled, ['index'=> 'foo', 'type' => 'bar', 'id' => 1234, 'body' => ['tamayo' => 'stretchy']]);
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
