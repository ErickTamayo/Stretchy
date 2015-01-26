<?php namespace Tamayo\Stretchy\Query\Clause;

use Closure;
use Illuminate\Support\Str;
use Tamayo\Stretchy\Query\Builder;

class Clause {

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
	 * Create a new clause.
	 *
	 * @param Builder $builder
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
		if ($type == 'subclause') {
			$value = $this->createSubclause($value);
		} elseif ($type == 'subquery') {
			$value = $this->createSubquery($value);
		}

		$this->constraints[] = compact('field', 'value', 'type');
	}

	/**
	 * Get the applied constraints in the clause.
	 *
	 * @return array
	 */
	public function getAffectedConstraints()
	{
		return $this->constraints;
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
			$this->setConstraint($key, $value);
		}
	}

	/**
	 * Create a sub clause and executes its callback.
	 *
	 * @param Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Clause
	 */
	public function createSubclause(Closure $callback)
	{
		$clause = new static($this->builder);

		$callback($clause);

		return $clause;
	}

	/**
	 * Create a sub query and execute its callback.
	 *
	 * @param Closure $callback
	 */
	public function createSubquery(Closure $callback)
	{
		$query = $this->builder->newInstance()->setSubquery();

		$callback($query);

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
		if ($this->isSubclause($constraint)) {
			return 'subclause';
		} elseif ($this->isSubquery($constraint)) {
			return 'subquery';
		}

		return 'constraint';
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
		return in_array($constraint, $this->subclauses);
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
