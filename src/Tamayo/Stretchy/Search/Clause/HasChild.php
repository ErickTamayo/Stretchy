<?php namespace Tamayo\Stretchy\Search\Clause;

class HasChild extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['query'];
}
