<?php namespace Tamayo\Stretchy\Search\Clause;

class Filtered extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['filter', 'query'];
}
