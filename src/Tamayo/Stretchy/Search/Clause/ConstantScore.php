<?php namespace Tamayo\Stretchy\Search\Clause;

class ConstantScore extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['filter', 'query'];
}
