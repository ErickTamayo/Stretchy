<?php namespace Tamayo\Stretchy\Search\Clause;

class DisMax extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['queries'];
}
