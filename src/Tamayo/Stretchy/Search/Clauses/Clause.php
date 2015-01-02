<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Illuminate\Support\Str;
use Tamayo\Stretchy\Search\Builder;

class Clause
{

	/**
	 * The value of the constraints.
	 *
	 * @var array
	 */
	protected $container = [];

	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = [];

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
	 * Set a constraint.
	 *
	 * @param string $constraint
	 * @param mixed $value
	 */
	public function __call($constraint, $arguments)
	{
		$constraint = Str::snake($constraint);

		if(! in_array($constraint, $this->constraints)) {
			throw new \InvalidArgumentException("Unavailable constraint: [{$constraint}]", 1);
		}

		$value = $arguments[0];

		$this->container[$constraint] = $value;

		return $this;
	}

	/**
	 * Get the applied constraints in the clause.
	 *
	 * @return array
	 */
	public function getAffectedConstraints()
	{
		return $this->container;
	}

	/**
	 * Set constraints for te clause.
	 *
	 * @param array $constraints
	 */
	public function setConstraints(array $constraints)
	{
		$this->constraints = $constraints;
	}

	/**
	 * Add raw constraints.
	 *
	 * @param array $constraints
	 */
	public function addRawConstraints(array $constraints)
	{
		foreach ($constraints as $key => $value) {
			if(! $this->isValidConstraint($key)) {
				throw new \InvalidArgumentException("Unavailable constraint: [{$key}]", 1);
			}
		}

		$this->container = array_merge($this->container, $constraints);
	}

	/**
	 * Checks if a constraint is valid.
	 *
	 * @param  string  $name
	 * @return boolean
	 */
	public function isValidConstraint($constraint)
	{
		return in_array($constraint, $this->constraints);
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

	/**
	 * Convert the clause to array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->container;
	}
}
