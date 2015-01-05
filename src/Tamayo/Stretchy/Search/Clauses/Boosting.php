<?php namespace Tamayo\Stretchy\Search\Clauses;

class Boosting extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['negative_boost'];

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['positive', 'negative'];

}
