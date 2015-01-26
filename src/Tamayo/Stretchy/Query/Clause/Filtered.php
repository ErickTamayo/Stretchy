<?php namespace Tamayo\Stretchy\Query\Clause;

class Filtered extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['filter', 'query'];
}
