<?php namespace Tamayo\Stretchy\Search\Clauses;

class Bool extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['minimum_should_match', 'boost'];

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['must', 'must_not', 'should'];
}
