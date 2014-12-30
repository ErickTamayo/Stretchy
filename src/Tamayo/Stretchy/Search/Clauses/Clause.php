<?php namespace Tamayo\Stretchy\Search\Clauses;

use Illuminate\Support\Str;

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
	 * Set a constraint.
	 *
	 * @param string $constraint
	 * @param mixed $value
	 */
	public function __call($constraint, $arguments)
	{
		$constraint = Str::snake($constraint);

		if(! in_array($constraint, $this->constraints)) {
			throw new \InvalidArgumentException("Unavailable constraint {$constraint}", 1);
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
	public function setConstraints($constraints)
	{
		$this->constraints = $constraints;
	}
}
