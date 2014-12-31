<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Builder;
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
	 * Must not sub clauses.
	 *
	 * @var array
	 */
	public $mustNot;

	/**
	 * Must sub clauses.
	 *
	 * @var array
	 */
	public $must;

	/**
	 * Should sub clauses.
	 *
	 * @var array
	 */
	public $should;

	/**
	 * The current builder instance.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	protected $builder;

	/**
	 * Create a new boolean clause.
	 *
	 * @param \Tamayo\Stretchy\Search\Builder $builder
	 * @param Grammar                     $grammar
	 */
	public function __construct(Builder $builder)
	{
		$this->builder = $builder;
	}
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

	/**
	 * Add a sub clause to a container an executes its callback.
	 *
	 * @param array   &$container
	 * @param Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function addSubClause(&$container, Closure $callback)
	{
		$query = $this->builder->newInstance()->setSubquery();

		$callback($query);

		$container = $query;

		return $this;
	}

}
