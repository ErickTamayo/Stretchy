<?php namespace Tamayo\Stretchy\Search\Clauses;

class HasParent extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['type', 'score_mode', 'parent_type'];

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['query'];
}
