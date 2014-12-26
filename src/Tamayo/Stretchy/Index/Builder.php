<?php namespace Tamayo\Stretchy\Index;

use Closure;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Index\Grammar;
use Tamayo\Stretchy\Index\Processor;

class Builder
{

	/**
	 * Elastic Connection.
	 *
	 * @var \Tamayo\Stretchy\Connection
	 */
	protected $connection;

	/**
	 * Index Grammar.
	 *
	 * @var \Tamayo\Stretchy\Index\Grammar
	 */
	protected $grammar;

	/**
	 * Index Processor.
	 *
	 * @var \Tamayo\Stretchy\Index\Processor
	 */
	protected $processor;

	/**
	 * Index Builder.
	 *
	 * @param \Tamayo\Stretchy\Connection $connection
	 * @param Grammar                     $grammar
	 */
	public function __construct(Connection $connection, Grammar $grammar, Processor $processor)
	{
		$this->connection = $connection;
		$this->grammar    = $grammar;
		$this->processor  = $processor;

		$this->grammar->setIndexPrefix($connection->getIndexPrefix());
	}

	/**
	 * Create a new index on Elastic.
	 *
	 * @param  string  $index
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Index\Blueprint
	 */
	public function create($index, Closure $callback)
	{
		$blueprint = $this->createBlueprint($index);

		$blueprint->create();

		$callback($blueprint);

		$this->build($blueprint);
	}

	/**
	 * Deletes an index on Elastic.
	 *
	 * @param  string $index
	 * @return \Tamayo\Stretchy\Index\Blueprint
	 */
	public function delete($index)
	{
		$blueprint = $this->createBlueprint($index);

		$blueprint->delete();

		$this->build($blueprint);
	}

	/**
	 * Get Settings of indices.
	 *
	 * @param  string|array $index
	 * @return mixed
	 */
	public function getSettings($index)
	{

		$prefix = $this->connection->getIndexPrefix();

		if (is_array($index)) {
			foreach ($index as $key => $value) {
				$index[$key] = $prefix.$value;
			}
		}
		else {
			$index = $prefix.$index;
		}

		$compiled = $this->grammar->compileGetSettings($index);

		return $this->processor->processGetSettings($this, $this->connection->indexGetSettings($compiled));
	}

	/**
	 * Create a new blueprint for builder.
	 *
	 * @param  string $index
	 * @param  Closure $callback
	 * @return \Tamayo\Stretch\Index\Blueprint
	 */
	protected function createBlueprint($index, Closure $callback = null)
	{
		return new Blueprint($index, $callback);
	}

	/**
	 * Execute the blueprint to build / modify the table.
	 *
	 * @param  \Tamayo\Stretch\Index\Blueprint $blueprint
	 * @return void
	 */
	protected function build(Blueprint $blueprint)
	{
		$blueprint->build($this->connection, $this->grammar);
	}
}
