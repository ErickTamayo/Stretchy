<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Index\Grammar;
use Tamayo\Stretchy\Index\Builder;
use Tamayo\Stretchy\Index\Processor;
use Tamayo\Stretchy\Index\Blueprint;

class IndexGrammarTest extends PHPUnit_Framework_TestCase
{

	public function testBasicCreateIndex()
	{
		$blueprint = new Blueprint('basic');
		$blueprint->create();

		$blueprint->shards(1);
		$blueprint->replicas(3);

		$json = $blueprint->toJson($this->getConnection(), $this->getGrammar());

		$this->assertEquals('{"index":"basic","body":{"settings":{"number_of_shards":1,"number_of_replicas":3}}}', $json);

	}

	public function testBasicCreateIndexWithPrefix()
	{
		$blueprint = new Blueprint('basic');
		$blueprint->create();

		$blueprint->shards(1);
		$blueprint->replicas(3);

		$grammar = $this->getGrammar();
		$grammar->setIndexPrefix('prefix_');

		$json = $blueprint->toJson($this->getConnection(), $grammar);

		$this->assertEquals('{"index":"prefix_basic","body":{"settings":{"number_of_shards":1,"number_of_replicas":3}}}', $json);

	}

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

	public function testCompileGetSettings()
	{
		$grammar = $this->getGrammar();

		$compiled = $grammar->compileGetSettings(['foo', 'bar']);

		$this->assertEquals($compiled, ['index' => ['foo', 'bar']]);
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
