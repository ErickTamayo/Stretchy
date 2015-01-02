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
	protected $constraints = ['query', 'cutoff_frequency', 'high_freq', 'low_freq', 'low_freq_operator', 'high_freq_operator', 'boost', 'analyzer', 'disable_coord'];

	/**
	 * Should sub clause.
	 *
	 * @var \Tamayo\Stretchy\Search\Builder
	 */
	public $minimumShouldMatch;

	/**
	 * Minimun should match sub clause.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Clauses\Bool
	 */
	public function minimumShouldMatch(Closure $callback)
	{
		$minimumShouldMatch = new Clause($this->builder);

		$minimumShouldMatch->setConstraints(['low_freq', 'high_freq']);

		$callback($minimumShouldMatch);

		$this->minimumShouldMatch = $minimumShouldMatch;

		return $this;
	}



}
