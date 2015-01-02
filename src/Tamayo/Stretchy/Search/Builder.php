<?php namespace Tamayo\Stretchy\Search;

use Closure;
use Illuminate\Support\Str;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Processor;
use Tamayo\Stretchy\Search\Clauses\Bool;
use Tamayo\Stretchy\Search\Clauses\Clause;
use Tamayo\Stretchy\Search\Clauses\Common;
use Tamayo\Stretchy\Search\Clauses\Boosting;
use Tamayo\Stretchy\Builder as BaseBuilder;

class Builder extends BaseBuilder {

	/**
	 * Search Processor.
	 *
	 * @var \Tamayo\Stretchy\Search\Processor
	 */
	protected $processor;

	/**
	 * Indicates if the builder is a subquery.
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
	 * Match constraints of the query.
	 *
	 * @var array
	 */
	public $match = [];

	/**
	 * Multi match constraints of the query.
	 *
	 * @var array
	 */
	public $multiMatch = [];

	/**
	 * Boolean constraints of the query.
	 *
	 * @var array
	 */
	public $bool = [];

	/**
	 * Boosting constraints of the query.
	 *
	 * @var array
	 */
	public $boosting = [];

	/**
	 * Common constraints of the query.
	 * @var array
	 */
	public $common = [];

	/**
	 * Create a new search builder.
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
	 * @param  string  $field
	 * @param  mixed   $value
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function match($field, $matching, Closure $callback = null, $type = 'boolean')
	{
		$match = new Clause($this);
		$match->setConstraints(['query', 'operator', 'zero_terms_query', 'cutoff_frequency', 'type', 'lenient', 'analizer']);

		// We check if the developer is providing aditional parameters
		if(isset($callback)) {
			$callback($match);
		}

		$match->query($matching);
		$match->type($type);

		$this->setStatement('match', $field, $match);

		return $this;
	}

	/**
	 * Elastic match phrase query.
	 *
	 * @param  string  		$field
	 * @param  mixed   		$value
	 * @param  Closure|null $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function matchPhrase($field, $matching, Closure $callback = null)
	{
		return $this->match($field, $matching, $callback, 'phrase');
	}

	/**
	 * Elastic match phrase prefix query.
	 *
	 * @param  string       $fiel
	 * @param  mixed       	$matching
	 * @param  Closure|null $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function matchPhrasePrefix($field, $matching, Closure $callback = null)
	{
		return $this->match($field, $matching, $callback, 'phrase_prefix');
	}

	/**
	 * Elastic multi match query.
	 *
	 * @param  array        $fields
	 * @param  string       $matching
	 * @param  Closure|null $callback
	 * @param  string       $type
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function multiMatch(array $fields, $matching, Closure $callback = null, $type = 'best_fields')
	{
		$match = new Clause($this);
		$match->setConstraints(['query', 'fields', 'type', 'tie_breaker', 'analyzer', 'boost', 'operator', 'minimum_should_match', 'fuzziness', 'prefix_length', 'max_expansions', 'rewrite', 'zero_terms_query', 'cutoff_frequency']);

		// We check if the developer is providing aditional parameters
		if(isset($callback)) {
			$callback($match);
		}

		$match->fields($fields);
		$match->query($matching);
		$match->type($type);

		$this->setStatement('multi_match', $fields, $match);

		return $this;
	}

	/**
	 * Elastic bool query.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function bool(Closure $callback)
	{
		$bool = new Bool($this);

		$callback($bool);

		$this->setStatement('bool', null, $bool);

		return $this;
	}

	/**
	 * Elastic boosting query.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function boosting(Closure $callback)
	{
		$boosting = new Boosting($this);

		$callback($boosting);

		$this->setStatement('boosting', null, $boosting);

		return $this;
	}

	/**
	 * Elastic common query.
	 *
	 * @param  string       $field
	 * @param  string       $value
	 * @param  Closure|null $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function common($field, $value, Closure $callback = null)
	{
		$common = new Common($this);

		// We check if the developer is providing aditional parameters
		if(isset($callback)) {
			$callback($common);
		}

		$common->query($value);

		$this->setStatement('common', $field, $common);

		return $this;
	}

	/**
	 * Set a single statement of the builder.
	 *
	 * @param string $type
	 * @param mixed $field
	 * @param mixed $value
	 * @return void
	 */
	protected function setSingleStatement($type, $field, $value)
	{
		$this->singleStatement = ['type' => Str::camel($type), 'field' => $field, 'value' => $value];
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
	 * @param mixed   $field
	 * @param mixed   $value
	 * @param boolean $single
	 * @return void
	 */
	protected function setStatement($type, $field, $value, $single = true)
	{
		if (! $this->isSubquery() && $single) {
			$this->setSingleStatement($type, $field, $value);
		}
		else
		{
			$container = Str::camel($type);

			$this->$container = array_merge($this->$container, [['field' => $field, 'value' => $value]]);
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

	/**
	 * Creates a new instance of the builder.
	 *
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function newInstance()
	{
		return new static($this->connection, $this->grammar, $this->processor);
	}

}
