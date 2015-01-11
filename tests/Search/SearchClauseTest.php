<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Query\Clause;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Processor;



class SearchClauseTest extends PHPUnit_Framework_TestCase
{
	public function testClause()
	{
		$clause = new Clause();

		$clause->setConstraints(['foo_bar']);

		$clause->fooBar('baz');

		$this->assertEquals([['field' => 'foo_bar', 'value' => 'baz', 'type' => 'constraint']], $clause->getAffectedConstraints());
	}

    /**
     * @expectedException InvalidArgumentException
     */
	public function testClauseException()
	{
		$clause = new Clause();

		$clause->fooBar('baz');
	}
}
