<?php namespace Tamayo\Stretchy\Search\Clauses;

class Indices extends Clause
{
	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = ['query', 'no_match_query'];
}
