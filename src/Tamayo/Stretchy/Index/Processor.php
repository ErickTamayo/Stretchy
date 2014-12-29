<?php namespace Tamayo\Stretchy\Index;

use Tamayo\Stretchy\Index\Builder;

class Processor {

	/**
	 * Process the result of a "GetSettings" in the indices api.
	 *
	 * @param  Builder $builder
	 * @param  mixed  $results
	 * @return array
	 */
	public function processGetSettings(Builder $builder, $results)
	{
		return $results;
	}

	/**
	 * Process the result of an insert in the indices api.
	 *
	 * @param  Builder $builder
	 * @param  mixed  $results
	 * @return array
	 */
	public function processInsert(Builder $builder, $results)
	{
		return $results;
	}
}
