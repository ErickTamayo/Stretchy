<?php namespace Tamayo\Stretchy\Search\Clause;

class Nested extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['query', 'filter'];
}
