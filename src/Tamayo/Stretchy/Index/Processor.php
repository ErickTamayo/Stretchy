<?php namespace Tamayo\Stretchy\Index;

use Tamayo\Stretchy\Index\Builder;

class Processor
{
	/**
	 * Process the result of a "GetSettings" in the indices api.
	 *
	 * @param  Builder $query
	 * @param  mixed  $results
	 * @return array
	 */
	public function processGetSettings(Builder $query, $results)
	{
		return $results;
	}
}
