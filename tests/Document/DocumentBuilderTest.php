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
