<?php namespace Tamayo\Stretchy;

use Tamayo\Stretchy\Grammar;
use Tamayo\Stretchy\Connection;

abstract class Builder {

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
     * The index wich the query is targeting.
     *
     * @var string
     */
    public $index = '*';

    /**
     * The document type in the index.
     *
     * @var string
     */
    public $type;

    /**
     * The id of the document.
     *
     * @var integer
     */
    public $id;

    /**
     * Page size.
     *
     * @var integer
     */
    public $size;

    /**
     * Skip from.
     *
     * @var integer
     */
    public $from;

	/**
	 * Elastic Builder.
	 *
	 * @param \Tamayo\Stretchy\Connection $connection
	 * @param \Tamayo\Stretchy\Grammar    $grammar
	 */
    public function __construct(Connection $connection, Grammar $grammar)
    {
    	$this->connection = $connection;
    	$this->grammar    = $grammar;

    	$this->grammar->setIndexPrefix($connection->getIndexPrefix());
    }

	/**
	 * Set the index of the builder.
	 *
	 * @param  string $name
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function index($name, $type = null)
	{
		$this->index = $name;

		return isset($type) ? $this->type($type) : $this;
	}

	/**
	 * Set the type of the document to look for in the specified index.
	 *
	 * @param  string $name
	 * @return \Tamayo\Stretchy\Builder
	 */
	public function type($name)
	{
		$this->type = $name;

		return $this;
	}

	/**
	 * Set an id for the document in the index
	 *
	 * @param  integer $id
	 * @return \Tamayo\Stretchy\Builder
	 */
	public function id($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Elastic search page size.
	 *
	 * @param  integer $value
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function size($value)
	{
		$this->size = $value;

		return $this;
	}

	/**
	 * Elastic search skip from.
	 *
	 * @param  integer $value
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function from($value)
	{
		$this->from = $value;

		return $this;
	}

	/**
	 * From alias.
	 *
	 * @param  integer $value
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function offset($value)
	{
		return $this->from($value);
	}

	/**
	 * Size alias.
	 *
	 * @param  integer $value
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function limit($value)
	{
		return $this->size($value);
	}

	/**
	 * Checks whether the index is defined or not.
	 *
	 * @return boolean
	 */
	public function indexIsDefined()
	{
		return $this->index != '*';
	}

	/**
	 * Checks whether the type is defined or not.
	 *
	 * @return boolean
	 */
	public function typeIsDefined()
	{
		return isset($this->type);
	}

}
