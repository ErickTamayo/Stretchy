<?php namespace Tamayo\Stretchy\Search\Clause;

use Exception;
use Illuminate\Support\Str;
use Tamayo\Stretchy\Search\Builder;

class Factory {
	/**
	 * Create a new Clause.
	 *
	 * @param string $clause
	 * @return \Tamayo\Stretchy\Query\Clause\Base
	 */
	public function make($clause, Builder $builder)
	{
		$clauseClass = __NAMESPACE__.'\\'.Str::studly($clause);

		//If clause class exists, it has an specific behaviour
		if (class_exists($clauseClass)) {
			return new $clauseClass($builder);
		}

		//Otherwise we call the base clause class
		return new Clause($builder);
	}
}
