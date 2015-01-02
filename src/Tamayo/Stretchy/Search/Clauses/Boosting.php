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
	 * Positive boosting sub clause.
	 *
	 * @var array
	 */
	public $positive;

	/**
	 * Negative boosting sub clause.
	 *
	 * @var array
	 */
	public $negative;

	/**
	 * Positive Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function positive(Closure $callback)
	{
		return $this->addSubClause($this->positive, $callback);
	}

	/**
	 * Positive Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function negative(Closure $callback)
	{
		return $this->addSubClause($this->negative, $callback);
	}

}
