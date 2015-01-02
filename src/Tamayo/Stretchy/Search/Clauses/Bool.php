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
	 * Must not sub clause.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $mustNot;

	/**
	 * Must sub clause.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $must;

	/**
	 * Should sub clause.
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
		return $this->addSubClause($this->must, $callback);
	}

	/**
	 * Must Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function mustNot(Closure $callback)
	{
		return $this->addSubClause($this->mustNot, $callback);
	}

	/**
	 * Must Clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function should(Closure $callback)
	{
		return $this->addSubClause($this->should, $callback);
	}

}
