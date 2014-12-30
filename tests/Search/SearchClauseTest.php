<?php

use Tamayo\Stretchy\Search\Clauses\Clause;

class SearchClauseTest extends PHPUnit_Framework_TestCase
{
	public function testClause()
	{
		$clause = new Clause;

		$clause->setConstraints(['foo_bar']);

		$clause->fooBar('baz');

		$this->assertEquals(['foo_bar' => 'baz'], $clause->getAffectedConstraints());
	}

    /**
     * @expectedException InvalidArgumentException
     */
	public function testClauseException()
	{
		$clause = new Clause;

		$clause->fooBar('baz');
	}
}
