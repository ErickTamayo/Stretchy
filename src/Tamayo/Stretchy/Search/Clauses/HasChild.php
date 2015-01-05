<?php namespace Tamayo\Stretchy\Search\Clauses;

class HasChild extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['type', 'score_mode', 'min_children', 'max_children'];

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['query'];
}
