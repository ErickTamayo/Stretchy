<?php namespace Tamayo\Stretchy\Search\Clauses;

use Closure;
use Tamayo\Stretchy\Search\Clauses\Clause;

class Common extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['query', 'cutoff_frequency', 'low_freq_operator', 'high_freq_operator', 'boost', 'analyzer', 'disable_coord'];

	/**
	 * Should sub clause.
	 *
	 * @var \Tamayo\Stretchy\Search\Clauses\Clause
	 */
	public $minimumShouldMatch;

	/**
	 * Minimun should match sub clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Clause
	 */
	public function minimumShouldMatch(Closure $callback)
	{
		return $this->addSubclause($this->minimumShouldMatch, ['low_freq', 'high_freq'], $callback);
	}

}
