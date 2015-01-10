<?php namespace Tamayo\Stretchy\Search\Clauses;

class Ids extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['type', 'values'];
}
