<?php namespace Tamayo\Stretchy\Query\Clause;

class ConstantScore extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['filter', 'query'];
}
