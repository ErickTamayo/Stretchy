<?php namespace Tamayo\Stretchy\Query\Clause;

class GeoShape extends Clause {
	/**
	 * Available sub clauses in the clause.
	 *
	 * @var array
	 */
	protected $subclauses = ['shape', 'indexed_shape'];
}
