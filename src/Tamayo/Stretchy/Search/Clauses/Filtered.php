<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Clauses\Clause;

class Filtered extends Clause
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
	public $query;

	/**
	 * Filter subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $filter;

	/**
	 * Query subquery.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function query(Closure $callback)
	{
		return $this->addSubquery($this->query, $callback);
	}

	/**
	 * Filter subquery.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function filter(Closure $callback)
	{
		return $this->addSubquery($this->filter, $callback);
	}

}
