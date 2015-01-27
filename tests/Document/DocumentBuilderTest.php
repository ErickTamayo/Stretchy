<?php

use Tamayo\Stretchy\Document\Builder;

class DocumentBuilderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		Mockery::close();
	}
		public function testBuildInsert()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');
		$connection->shouldReceive('getIndexPrefix')->once();
		$connection->shouldReceive('documentInsert')->once();

		$grammar = Mockery::mock('Tamayo\Stretchy\Document\Grammar');
		$grammar->shouldReceive('setIndexPrefix')->once();
		$grammar->shouldReceive('compileInsert')->once();

		$processor = Mockery::mock('Tamayo\Stretchy\Document\Processor');
		$processor->shouldReceive('processInsert')->once();

		$builder = new Builder($connection, $grammar, $processor);

		$builder->index('foo')->type('bar')->insert(['tamayo' => 'stretchy']);
	}

	public function testBuildUpdate()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');
		$connection->shouldReceive('getIndexPrefix')->once();
		$connection->shouldReceive('documentUpdate')->once();

		$grammar = Mockery::mock('Tamayo\Stretchy\Document\Grammar');
		$grammar->shouldReceive('setIndexPrefix')->once();
		$grammar->shouldReceive('compileUpdate')->once();

		$processor = Mockery::mock('Tamayo\Stretchy\Document\Processor');
		$processor->shouldReceive('processUpdate')->once();

		$builder = new Builder($connection, $grammar, $processor);

		$builder->index('foo')->type('bar')->update(['tamayo' => 'stretchy']);
	}

	public function testBuildDelete()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');
		$connection->shouldReceive('getIndexPrefix')->once();
		$connection->shouldReceive('documentDelete')->once();

		$grammar = Mockery::mock('Tamayo\Stretchy\Document\Grammar');
		$grammar->shouldReceive('setIndexPrefix')->once();
		$grammar->shouldReceive('compileDelete')->once();

		$processor = Mockery::mock('Tamayo\Stretchy\Document\Processor');
		$processor->shouldReceive('processDelete')->once();

		$builder = new Builder($connection, $grammar, $processor);

		$builder->index('foo')->type('bar')->delete();
	}

	public function testBuildGet()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');
		$connection->shouldReceive('getIndexPrefix')->once();
		$connection->shouldReceive('documentGet')->once();

		$grammar = Mockery::mock('Tamayo\Stretchy\Document\Grammar');
		$grammar->shouldReceive('setIndexPrefix')->once();
		$grammar->shouldReceive('compileGet')->once();

		$processor = Mockery::mock('Tamayo\Stretchy\Document\Processor');
		$processor->shouldReceive('processGet')->once();

		$builder = new Builder($connection, $grammar, $processor);

		$builder->index('foo')->type('bar')->get();
	}

    /**
     * @expectedException Tamayo\Stretchy\Exceptions\IndexMustBeDefinedException
     */
	public function testBuilderIndexNotDefinedException()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');
		$connection->shouldReceive('getIndexPrefix')->once();

		$grammar = Mockery::mock('Tamayo\Stretchy\Document\Grammar');
		$grammar->shouldReceive('setIndexPrefix')->once();

		$processor = Mockery::mock('Tamayo\Stretchy\Document\Processor');

		$builder = new Builder($connection, $grammar, $processor);

		$builder->insert(['tamayo' => 'stretchy']);
	}

    /**
     * @expectedException Tamayo\Stretchy\Exceptions\TypeMustBeDefinedException
     */
	public function testBuilderTypeNotDefinedException()
	{
		$connection = Mockery::mock('Tamayo\Stretchy\Connection');
		$connection->shouldReceive('getIndexPrefix')->once();

		$grammar = Mockery::mock('Tamayo\Stretchy\Document\Grammar');
		$grammar->shouldReceive('setIndexPrefix')->once();

		$processor = Mockery::mock('Tamayo\Stretchy\Document\Processor');

		$builder = new Builder($connection, $grammar, $processor);

		$builder->index('foo')->insert(['tamayo' => 'stretchy']);
	}
}
