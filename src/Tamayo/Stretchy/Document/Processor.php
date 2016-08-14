<?php

namespace Tamayo\Stretchy\Document;

use Tamayo\Stretchy\Document\Builder;

class Processor {

	/**
	 * Process the result of an insert.
	 *
	 * @param  Builder $builder
	 * @param  mixed  $results
	 * @return array
	 */
	public function processInsert(Builder $builder, $results)
	{
		return $results;
	}

	/**
	 * Process the result of a update.
	 *
	 * @param  Builder $builder
	 * @param  mixed  $results
	 * @return array
	 */
	public function processUpdate(Builder $builder, $results)
	{
		return $results;
	}

	/**
	 * Process the result of a delete.
	 *
	 * @param  Builder $builder
	 * @param  mixed  $results
	 * @return array
	 */
	public function processDelete(Builder $builder, $results)
	{
		return $results;
	}

	/**
	 * Process the result of a delete.
	 *
	 * @param  Builder $builder
	 * @param  mixed  $results
	 * @return array
	 */
	public function processGet(Builder $builder, $results)
	{
		return $results;
	}

}
