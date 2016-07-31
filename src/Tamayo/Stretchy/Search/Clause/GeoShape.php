<?php namespace Tamayo\Stretchy\Search\Clause;

class GeoShape extends Clause {
	/**
	 * Available sub clauses in the clause.
	 *
	 * @var array
	 */
	protected $subclauses = ['shape', 'indexed_shape'];
}
