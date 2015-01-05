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
	 * Available sub clauses in the clause.
	 *
	 * @var array
	 */
	protected $subclauses = [];

	/**
	 * Available sub queries in the clause.
	 *
	 * @var array
	 */
	protected $subqueries = [];

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
	public function __call($method, $arguments)
	{
		$constraint = Str::snake($method);
		$value      = $arguments[0];
		$type 		= $this->constraintType($constraint);

		if (!isset($type)) {
			throw new \InvalidArgumentException("Unavailable constraint: [{$constraint}]", 1);
		}

		$this->setConstraint($constraint, $value, $type);

		return $this;
	}

	/**
	 * Set a constraint.
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return void
	 */
	protected function setConstraint($field, $value, $type = 'constraint')
	{
		switch ($type) {
			case 'subclause':
				$value = $this->createSubclause($this->subclauses[$field], $value);
				$this->container[] = array_merge(compact('field', 'type'), ['value' => $value]);
				break;

			case 'subquery':
				$value = $this->createSubquery($value);
				$this->container[] = array_merge(compact('field', 'type'), ['value' => $value]);
				break;

			default:
				$this->container[] = compact('field', 'value', 'type');
				break;
		}
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
	 * @return void
	 */
	public function addRawConstraints(array $constraints)
	{
		foreach ($constraints as $key => $value) {
			if(! $this->isValidConstraint($key)) {
				throw new \InvalidArgumentException("Unavailable constraint: [{$key}]", 1);
			}

			$this->setConstraint($key, $value);
		}
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
	 * Create a sub clause and executes its callback.
	 *
	 * @param array  &$container
	 * @param Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Clause
	 */
	public function createSubclause(array $constraints, Closure $callback)
	{
		$clause = new static($this->builder);

		$clause->setConstraints($constraints);

		$callback($clause);

		return $clause;
	}

	/**
	 * Create a sub query and execute its callback.
	 *
	 * @param array  &$container
	 * @param Closure $callback
	 */
	public function createSubquery(Closure $callback)
	{
		$query = $this->builder->newInstance()->setSubquery();

		$callback($query);

		$container = $query;

		return $query;
	}

	/**
	 * Convert the clause to array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$array = array();

		foreach ($this->getAffectedConstraints() as $constraint) {
			if ($constraint['type'] == 'constraint') {
				$array = array_merge($array, [$constraint['field'] => $constraint['value']]);
			}
			else {
				$array = array_merge([$constraint['field'] => $constraint['value']->toArray()], $array);
			}
		}

		return $array;
	}

	/**
	 * Get the type of the constraint.
	 *
	 * @param  string $constraint
	 * @return string|null
	 */
	protected function constraintType($constraint)
	{
		if($this->isConstraint($constraint)) {
			return 'constraint';
		} elseif ($this->isSubclause($constraint)) {
			return 'subclause';
		} elseif ($this->isSubquery($constraint)) {
			return 'subquery';
		}

		return null;
	}

	/**
	 * Get the available constraints.
	 *
	 * @return array
	 */
	protected function isConstraint($constraint)
	{
		return in_array($constraint, $this->constraints);
	}

	/**
	 * Get the available sub clauses.
	 *
	 * @return array
	 */
	protected function isSubclause($constraint)
	{
		return in_array($constraint, array_keys($this->subclauses));
	}

	/**
	 * Get the available sub queries.
	 *
	 * @return array
	 */
	protected function isSubquery($constraint)
	{
		return in_array($constraint, $this->subqueries);
	}
}
