<?php namespace Tamayo\Stretchy\Index;

use Tamayo\Stretchy\Connection;

class Grammar
{

	/**
	 * The index prefix.
	 *
	 * @var string
	 */
	protected $indexPrefix;

	/**
	 * Compile the create Index command.
	 *
	 * @return array
	 */
	public function compileIndexCreate(Blueprint $blueprint, Connection $connection)
	{
		$compiled = array();

		$compiled['index'] = $this->getIndexName($blueprint);

		$compiled['body']  = $this->compileSettings($blueprint);

		return $compiled;
	}


	/**
	 * Get the index name.
	 *
	 * @param  Blueprint  $blueprint
	 * @param  Connection $connection
	 * @return string
	 */
	public function getIndexName(Blueprint $blueprint)
	{
		return $this->getIndexPrefix().$blueprint->getIndex();
	}

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
	 * Compile the settings for the index.
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public function compileSettings(Blueprint $blueprint)
	{
		$compiled = array();

		$compiled['settings'] = array_merge($this->compileShards($blueprint), $this->compileReplicas($blueprint));

		return $compiled;
	}

	/**
	 * Compile the shard count.
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public function compileShards(Blueprint $blueprint)
	{
		$compiled = array();

		$compiled['number_of_shards'] = $blueprint->getShards();

		return $compiled;
	}

	/**
	 * Compile the replica count.
	 *
	 * @param  Blueprint $blueprint
	 * @return array
	 */
	public function compileReplicas(Blueprint $blueprint)
	{
		$compiled = array();

		$compiled['number_of_replicas'] = $blueprint->getReplicas();

		return $compiled;
	}

}
