<?php namespace Tamayo\Stretchy\Search\Clauses;

class MatchAll extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['boost'];
}
