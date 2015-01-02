<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Clauses\Clause;

class ConstantScore extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['boost'];

	/**
	 * Filter subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $filter;

	/**
	 * Query subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $query;

	/**
	 * Filter subquery.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function filter(Closure $callback)
	{
		return $this->addSubquery($this->filter, $callback);
	}


	public function query(Closure $callback)
	{
		return $this->addSubquery($this->query, $callback);
	}

}
