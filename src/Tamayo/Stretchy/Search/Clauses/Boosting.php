<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Clauses\Clause;

class Boosting extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['negative_boost'];

	/**
	 * Positive boosting subquery.
	 *
	 * @var array
	 */
	public $positive;

	/**
	 * Negative boosting subquery.
	 *
	 * @var array
	 */
	public $negative;

	/**
	 * Positive Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function positive(Closure $callback)
	{
		return $this->addSubquery($this->positive, $callback);
	}

	/**
	 * Positive Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function negative(Closure $callback)
	{
		return $this->addSubquery($this->negative, $callback);
	}

}
