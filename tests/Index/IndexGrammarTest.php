<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Index\Grammar;
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

    public function testCompileGetSettings()
    {
    	$grammar = $this->getGrammar();

    	$compiled = $grammar->compileGetSettings(['tamayo', 'stretchy']);

    	$this->assertEquals($compiled, ['index' => ['tamayo', 'stretchy']]);
    }

    public function getGrammar()
    {
        return new Grammar;
    }

    public function getConnection()
    {
        return Mockery::mock('Tamayo\Stretchy\Connection');
    }
}
