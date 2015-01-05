<?php namespace Tamayo\Stretchy\Search\Clauses;

class GeoShape extends Clause
{
	/**
	 * Available sub clauses in the clause.
	 *
	 * @var array
	 */
	protected $subclauses = ['shape' => ['type', 'coordinates'], 'indexed_shape' => ['id', 'type', 'index', 'location', 'path']];
}
