<?php

use Tamayo\Stretchy\Index\Blueprint;

class IndexBlueprintTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        Mockery::close();
    }

    public function testBuildCreateIndex()
    {
        $blueprint = new Blueprint('index');
        $blueprint->create();

        $connection = Mockery::mock('Tamayo\Stretchy\Connection');
        $connection->shouldReceive('indexCreate')->once();

        $grammar = Mockery::mock('Tamayo\Stretchy\Index\Grammar');
        $grammar->shouldReceive('compileIndexCreate')->once();

        $blueprint->build($connection, $grammar);
    }

    public function testBuildDeleteIndex()
    {
        $blueprint = new Blueprint('index');
        $blueprint->delete();

        $connection = Mockery::mock('Tamayo\Stretchy\Connection');
        $connection->shouldReceive('indexDelete')->once();

        $grammar = Mockery::mock('Tamayo\Stretchy\Index\Grammar');
        $grammar->shouldReceive('compileIndexDelete')->once();

        $blueprint->build($connection, $grammar);
    }
}
