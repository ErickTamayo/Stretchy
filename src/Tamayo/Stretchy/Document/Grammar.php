<?php

namespace Tamayo\Stretchy\Document;

use Tamayo\Stretchy\Document\Builder;
use Tamayo\Stretchy\Grammar as BaseGrammar;

class Grammar extends BaseGrammar {

	/**
	 * Compile index clause.
	 *
	 * @param  Builder $builder
	 * @param  array   $payload
	 * @return array
	 */
	public function compileInsert(Builder $builder, array $payload)
	{
		$compiled = $this->compile('body', $payload);

		$compiled = array_merge($this->compileHeader($builder), $compiled);

		return $compiled;
	}

	/**
	 * Compile update clause.
	 *
	 * @param  Builder $builder
	 * @param  array   $payload
	 * @return array
	 */
	public function compileUpdate(Builder $builder, $payload)
	{
		$compiled = $this->compile('body', $this->compile('doc', $payload));

		$compiled = array_merge($this->compileHeader($builder), $compiled);

		return $compiled;
	}

	/**
	 * Compile delete clause.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileDelete(Builder $builder)
	{
		return $this->compileHeader($builder);
	}

	/**
	 * Compile get clause.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileGet(Builder $builder)
	{
		return $this->compileHeader($builder);
	}

}
