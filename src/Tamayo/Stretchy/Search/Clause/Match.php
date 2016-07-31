<?php namespace Tamayo\Stretchy\Search\Clause;

class Match extends Clause {

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['must', 'must_not', 'should'];
}
