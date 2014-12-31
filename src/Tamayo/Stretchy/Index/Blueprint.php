<?php namespace Tamayo\Stretchy\Index;

use Closure;
use Illuminate\Support\Fluent;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Index\Grammar;

class Blueprint {

	/**
	 * Elastic Index Name.
	 *
	 * @var string
	 */
	protected $index;

	/**
	 * Shards quantity.
	 *
	 * @var
	 */
	protected $shards;

	/**
	 * Replicas quantity.
	 *
	 * @var
	 */
	protected $replicas;

	/**
	 * Index fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * The command to run to Eslastic.
	 *
	 * @var string
	 */
	protected $command;

	/**
	 * Creation Date.
	 *
	 * @var integer
	 */
	protected $creationDate;

	/**
	 * Create a new Index Blueprint.
	 *
	 * @param string  $index
	 * @param Closure $callback
	 */
	public function __construct($index, Closure $callback = null)
	{
		$this->index = $index;

		if ( ! is_null($callback)) $callback($this);
	}

	/**
	 * Indicates that the index needs to be created.
	 *
	 * @return void
	 */
	public function create()
	{
		$this->command = 'indexCreate';
	}

	/**
	 * Indicates that the index needs to be deleted.
	 *
	 * @return void
	 */
	public function delete()
	{
		$this->command = 'indexDelete';
	}

	/**
	 * Specify number of index shards.
	 *
	 * @param  integer $quantity
	 * @return \Tamayo\Stretchy\Index\Builder
	 */
	public function shards($quantity)
	{
		$this->shards = $quantity;

		return $this;
	}

	/**
	 * Specify number of index replicas.
	 *
	 * @param  integer $quantity
	 * @return \Tamayo\Stretchy\Index\Builder
	 */
	public function replicas($quantity)
	{
		$this->replicas = $quantity;

		return $this;
	}

	/**
	 * Set Index creation timestamp.
	 *
	 * @param  integer $date
	 * @return \Tamayo\Stretchy\Index\Builder
	 */
	public function creationDate($date)
	{
		$this->creationDate = $date;

		return $this;
	}

	/**
	 * Execute the index operation to Elastic.
	 *
	 * @param  Connection $connection
	 * @param  Grammar    $grammar
	 * @return void
	 */
	public function build(Connection $connection, Grammar $grammar)
	{
		$method = $this->command;

		$connection->$method($this->toArray($connection, $grammar));
	}

	/**
	 * Get the raw json statements for the blueprint.
	 *
	 * @param  Connection $connection
	 * @param  Grammar    $grammar
	 * @return string
	 */
	public function toJson(Connection $connection, Grammar $grammar, $options = 0)
	{
		return json_encode($this->toArray($connection, $grammar), $options);
	}

	/**
	 * Get the raw array statements for the blueprint.
	 *
	 * @param  Connection $connection
	 * @param  Grammar    $grammar
	 * @return array
	 */
	public function toArray(Connection $connection, Grammar $grammar)
	{
		$method = 'compile'.ucfirst($this->command);

		return $grammar->$method($this, $connection);
	}

	/**
	 * Get the name of the index.
	 *
	 * @return string
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * Get the shards count.
	 *
	 * @return integer
	 */
	public function getShards()
	{
		return $this->shards;
	}

	/**
	 * Get the replicas count.
	 *
	 * @return integer
	 */
	public function getReplicas()
	{
		return $this->replicas;
	}

}
