<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Clauses\Clause;

class DisMax extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['tie_breaker', 'boost'];

	/**
	 * Queries subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $queries;

	/**
	 * Queries subquery.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function queries(Closure $callback)
	{
		return $this->addSubquery($this->queries, $callback);
	}

}
