<?php namespace Tamayo\Stretchy\Search;

use Closure;
use Tamayo\Stretchy\Connection;
use Illuminate\Support\Pluralizer;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Processor;
use Tamayo\Stretchy\Search\Clauses\Match;
use Tamayo\Stretchy\Builder as BaseBuilder;

class Builder extends BaseBuilder {

	/**
	 * Search Processor.
	 *
	 * @var \Tamayo\Stretchy\Search\Processor
	 */
	protected $processor;

	/**
	 * Determines if the builder is a subquery.
	 *
	 * @var boolean
	 */
	protected $isSubquery = false;

	/**
	 * Single statement constraint.
	 *
	 * @var array
	 */
	public $singleStatement;

	/**
	 * The matching constraints of the query.
	 *
	 * @var array
	 */
	public $matches;

	/**
	 * Search Builder.
	 *
	 * @param \Tamayo\Stretchy\Connection $connection
	 * @param Grammar                     $grammar
	 */
	public function __construct(Connection $connection, Grammar $grammar, Processor $processor)
	{
		parent::__construct($connection, $grammar);

		$this->processor  = $processor;
	}

	/**
	 * Checks if this is a subquery.
	 *
	 * @return boolean
	 */
	public function isSubquery()
	{
		return $this->isSubquery;
	}

	/**
	 * Declares the builder as a subquery.
	 *
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function setSubquery()
	{
		$this->isSubquery = true;

		return $this;
	}

	/**
	 * Index alias.
	 *
	 * @param  string  $index
	 * @return \Tamayo\Stretchy\Index\Blueprint
	 */
	public function search($index)
	{
		return $this->index($index);
	}

	/**
	 * Elastic Match Query.
	 *
	 * @param  string $field
	 * @param  mixed $value
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function match($field, $matching, Closure $parameters = null)
	{
		$match = new Match;

		// We check if the developer is providing aditional parameters
		if ($matching instanceof Closure) {
			$matching($match);
		}
		elseif (isset($parameters)) {
			$parameters($match);
			$match->query($matching);
		}
		else{
			$match->query($matching);
		}

		$this->setStatement('match', $field, $match);

		return $this;
	}

	/**
	 * Set a single statement of the builder.
	 *
	 * @param string $type
	 * @param mixed $value
	 */
	protected function setSingleStatement($type, $field, $value)
	{
		$this->singleStatement = ['type' => $type, 'statement' => [$field => $value]];
	}

	/**
	 * Get the single statement of the builder.
	 *
	 * @return array
	 */
	public function getSingleStatement()
	{
		return $this->singleStatement;
	}

	/**
	 * Set a statement to the builder.
	 *
	 * @param string  $type
	 * @param string  $field
	 * @param mixed   $value
	 * @param boolean $single
	 */
	protected function setStatement($type, $field, $value, $single = true)
	{
		if (! $this->isSubquery() && $single) {
			$this->setSingleStatement($type, $field, $value);
		}
		else
		{
			$container = Pluralizer::plural($type);

			$this->$container = array_merge($this->$container, [$field => $value]);
		}
	}

	/**
	 * Execute the search.
	 *
	 * @return array
	 */
	public function get()
	{
		$compiled = $this->grammar->compileSearch($this);

		return $this->processor->processSearch($this, $this->connection->search($compiled));
	}

	/**
	 * Compile the query to array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->grammar->compileSearch($this);
	}

	/**
	 * Compile the query to json.
	 *
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

}
