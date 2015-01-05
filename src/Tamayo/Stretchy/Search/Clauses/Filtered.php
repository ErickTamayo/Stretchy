<?php namespace Tamayo\Stretchy\Search\Clauses;

class Filtered extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['tie_breaker', 'boost'];

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['filter', 'query'];
}
