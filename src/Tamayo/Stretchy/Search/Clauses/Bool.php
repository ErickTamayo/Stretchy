<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Clauses\Clause;

class Bool extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['minimum_should_match', 'boost'];

	/**
	 * Must not subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $mustNot;

	/**
	 * Must subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $must;

	/**
	 * Should subquery.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $should;

	/**
	 * Must Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function must(Closure $callback)
	{
		return $this->addSubquery($this->must, $callback);
	}

	/**
	 * Must Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function mustNot(Closure $callback)
	{
		return $this->addSubquery($this->mustNot, $callback);
	}

	/**
	 * Must Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function should(Closure $callback)
	{
		return $this->addSubquery($this->should, $callback);
	}

}
