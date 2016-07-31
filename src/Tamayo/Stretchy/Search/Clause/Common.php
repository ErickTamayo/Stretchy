<?php namespace Tamayo\Stretchy\Search\Clause;

class Common extends Clause {

	/**
	 * Available sub clauses in the clause.
	 *
	 * @var array
	 */
	protected $subclauses = ['minimum_should_match'];
}
