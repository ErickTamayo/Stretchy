<?php namespace Tamayo\Stretchy\Search\Clauses;

use Tamayo\Stretchy\Search\Clauses\Clause;

class Match extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['query', 'operator', 'zero_terms_query', 'cutoff_frequency'];
}
