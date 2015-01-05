<?php namespace Tamayo\Stretchy\Search\Clauses;

class Common extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['query', 'cutoff_frequency', 'low_freq_operator', 'high_freq_operator', 'boost', 'analyzer', 'disable_coord'];

	/**
	 * Available sub clauses in the clause.
	 *
	 * @var array
	 */
	protected $subclauses = ['minimum_should_match' => ['low_freq', 'high_freq']];
}
