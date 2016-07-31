<?php

use Tamayo\Stretchy\Search\Clause\Clause;

class SearchClauseTest extends PHPUnit_Framework_TestCase
{
	public function testClause()
	{
		$clause = new Clause(Mockery::mock('Tamayo\Stretchy\Search\Builder'));

		$clause->fooBar('baz');

		$this->assertEquals([['field' => 'foo_bar', 'value' => 'baz', 'type' => 'constraint']], $clause->getAffectedConstraints());
	}
}
