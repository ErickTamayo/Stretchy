<?php namespace Tamayo\Stretchy\Query\Clause;

class Boosting extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['positive', 'negative'];
}
