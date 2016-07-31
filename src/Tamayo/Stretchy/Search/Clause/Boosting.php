<?php namespace Tamayo\Stretchy\Search\Clause;

class Boosting extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['positive', 'negative'];
}
