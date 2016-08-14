<?php

namespace Tamayo\Stretchy;

use Tamayo\Stretchy\Builder;

class Grammar {

	/**
	 * The index prefix.
	 *
	 * @var string
	 */
	protected $indexPrefix;

	/**
	 * Set the index prefix.
	 *
	 * @param sting $prefix
	 */
	public function setIndexPrefix($prefix)
	{
		$this->indexPrefix = $prefix;
	}

	/**
	 * Get the index prefix.
	 *
	 * @return string
	 */
	public function getIndexPrefix()
	{
		return $this->indexPrefix;
	}

	/**
	 * Compile the the indices.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileBuilderIndices(Builder $builder)
	{
		// If is a subquery, don't compile indices
		if ($builder->isNested()) {
			return [];
		}

		if (! count($builder->indices)) {
			return ['index' => '*'];
		}

		return ['index' => implode(',', $builder->indices)];
	}

	/**
	 * Compile the the index.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileIndex(Builder $builder)
	{
		$index = $builder->index;

		if (is_array($index)) {

			foreach ($index as $key => $value) {
					$index[$key] = $this->getIndexPrefix().$value;
				}

		} else {
			$index = $this->getIndexPrefix().$index;
		}

		return $this->compile('index', $index);
	}

	/**
	 * Compile document type.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileType(Builder $builder)
	{
		return $this->compile('type', $builder->type);
	}

	/**
	 * Compile size statement.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileSize($builder)
	{
		return $this->compile('size', $builder->size);
	}

	/**
	 * Compile from statement.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileFrom($builder)
	{
		return $this->compile('from', $builder->from);
	}

	/**
	 * Compile document id.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileId(Builder $builder)
	{
		return $this->compile('id', $builder->id);
	}

	/**
	 * Compiles the value into an array with the key if the statement exists.
	 *
	 * @param  string $type
	 * @param  array $statement
	 * @param  mixed $class
	 * @return array
	 */
	public function compile($type, $statement, $class = null)
	{
		return isset($statement) ? [$type => $statement] : [];
	}

	public function compileHeader(Builder $builder)
	{
		$compiled = array_merge(
			$this->compileIndex($builder),
			$this->compileType($builder),
			$this->compileId($builder),
			$this->compileSize($builder),
			$this->compileFrom($builder)
		);

		return $compiled;
	}
}
