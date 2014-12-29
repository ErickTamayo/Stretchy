<?php namespace Tamayo\Stretchy\Index;

use Tamayo\Stretchy\Index\Builder;
use Tamayo\Stretchy\Index\Blueprint;
use Tamayo\Stretchy\Grammar as BaseGrammar;

class Grammar extends BaseGrammar {

	/**
	 * Compile the create Index command.
	 *
	 * @return array
	 */
	public function compileIndexCreate(Blueprint $blueprint)
	{
		$compiled = array_merge(
				$this->compile('index', $this->getIndexName($blueprint)),
				$this->compile('body', $this->compileSettings($blueprint))
			);

		return $compiled;
	}

	/**
	 * Compile the delete Index command.
	 *
	 * @param  Blueprint  $blueprint
	 * @return array
	 */
	public function compileIndexDelete(Blueprint $blueprint)
	{
		return $this->compile('index', $this->getIndexName($blueprint));
	}

	/**
	 * Compile an insert command.
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
	 * Compile Get Settings.
	 *
	 * @param  string|array $indices
	 * @return array
	 */
	public function compileGetSettings($indices)
	{
		return $this->compile('index', (array) $indices);
	}

	/**
	 * Get the index name.
	 *
	 * @param  Blueprint  $blueprint
	 * @return string
	 */
	public function getIndexName(Blueprint $blueprint)
	{
		return $this->getIndexPrefix().$blueprint->getIndex();
	}

	/**
	 * Compile the settings for the index.
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public function compileSettings(Blueprint $blueprint)
	{
		return $this->compile('settings', array_merge($this->compileShards($blueprint), $this->compileReplicas($blueprint)));
	}

	/**
	 * Compile the shard count.
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public function compileShards(Blueprint $blueprint)
	{
		return $this->compile('number_of_shards', $blueprint->getShards());
	}

	/**
	 * Compile the replica count.
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public function compileReplicas(Blueprint $blueprint)
	{
		return $this->compile('number_of_replicas', $blueprint->getReplicas());
	}

}
