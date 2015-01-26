<?php namespace Tamayo\Stretchy\Search;

use Closure;
use Illuminate\Support\Str;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Processor;
use Tamayo\Stretchy\Query\Clause\Factory;
use Tamayo\Stretchy\Builder as BaseBuilder;

class Builder extends BaseBuilder {

	/**
	 * Search Processor.
	 *
	 * @var \Tamayo\Stretchy\Search\Processor
	 */
	protected $processor;

	/**
	 * The clause factory instance.
	 *
	 * @var \Tamayo\Stretchy\Query\Clause\Factory
	 */
	protected $clauseFactory;

	/**
	 * Indicates if the builder is a subquery.
	 *
	 * @var boolean
	 */
	protected $isSubquery = false;

	/**
	 * Statements set by the builder.
	 *
	 * @var array
	 */
	protected $statements = [];

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
	public $match;

	/**
	 * Multi match constraints of the query.
	 *
	 * @var array
	 */
	public $multiMatch;

	/**
	 * Boolean constraints of the query.
	 *
	 * @var array
	 */
	public $bool;

	/**
	 * Boosting constraints of the query.
	 *
	 * @var array
	 */
	public $boosting;

	/**
	 * Common constraints of the query.
	 *
	 * @var array
	 */
	public $common;

	/**
	 * Constant score constraints of the query.
	 *
	 * @var array
	 */
	public $constantScore;

	/**
	 * Dis max constraints of the query.
	 *
	 * @var array
	 */
	public $disMax;

	/**
	 * Filtered constraints of the query.
	 *
	 * @var array
	 */
	public $filtered;

	/**
	 * Fuzzy like this constraints of the query.
	 *
	 * @var array
	 */
	public $fuzzyLikeThis;

	/**
	 * Fuzzy like this constraints of the query.
	 *
	 * @var array
	 */
	public $fuzzyLikeThisField;

	/**
	 * Fuzzy constraints of the query.
	 *
	 * @var array
	 */
	public $fuzzy;

	/**
	 * Range constraints of the query.
	 *
	 * @var array
	 */
	public $range;

	/**
	 * Term constraints of the query.
	 *
	 * @var array
	 */
	public $term;

	/**
	 * Create a new search builder.
	 *
	 * @param \Tamayo\Stretchy\Connection $connection
	 * @param Grammar                     $grammar
	 */
	public function __construct(Connection $connection, Grammar $grammar, Processor $processor, Factory $clauseFactory)
	{
		parent::__construct($connection, $grammar);

		$this->processor = $processor;
		$this->clauseFactory = $clauseFactory;
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
	 * @param  string  			  $field
	 * @param  mixed  			  $value
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function match($field, $matching, $parameters = null, $type = 'boolean')
	{
		$match = $this->newClause('match');

		$this->addClauseParameters($match, $parameters);

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
	 * @param  array        	  $fields
	 * @param  string       	  $matching
	 * @param  Closure|array|null $parameters
	 * @param  string       	  $type
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function multiMatch(array $fields, $matching, Closure $parameters = null, $type = 'best_fields')
	{
		$match = $this->newClause('multi_match');

		$this->addClauseParameters($match, $parameters);

		$match->fields($fields);

		$match->query($matching);

		$match->type($type);

		$this->setStatement('multi_match', null, $match);

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
		$bool = $this->newClause('bool');

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
		$boosting = $this->newClause('boosting');

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
	public function common($field, $value, $parameters = null)
	{
		$common = $this->newClause('common');

		$this->addClauseParameters($common, $parameters);

		$common->query($value);

		$this->setStatement('common', $field, $common);

		return $this;
	}

	/**
	 * Elastic constant score query.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function constantScore(Closure $callback)
	{
		$constantScore = $this->newClause('constant_score');

		$callback($constantScore);

		$this->setStatement('constant_score', null, $constantScore);

		return $this;
	}

	/**
	 * Elastic dis max query.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function disMax(Closure $callback)
	{
		$disMax = $this->newClause('dis_max');

		$callback($disMax);

		$this->setStatement('dis_max', null, $disMax);

		return $this;
	}

	/**
	 * Elastic filtered query.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function filtered(Closure $callback)
	{
		$filtered = $this->newClause('filtered');

		$callback($filtered);

		$this->setStatement('filtered', null, $filtered);

		return $this;
	}

	public function fuzzyLikeThis(array $fields, $value, $parameters = null)
	{
		$fuzzyLikeThis = $this->newClause('fuzzy_like_this');

		$this->addClauseParameters($fuzzyLikeThis, $parameters);

		$fuzzyLikeThis->likeText($value);

		$fuzzyLikeThis->fields($fields);

		$this->setStatement('fuzzy_like_this', null, $fuzzyLikeThis);

		return $this;
	}

	/**
	 * Fuzzy like this field query.
	 *
	 * @param  string 		 $field
	 * @param  mixed 		 $value
	 * @param  Closure|array $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function fuzzyLikeThisField($field, $value, $parameters = null)
	{
		$fuzzyLikeThisField = $this->newClause('fuzzy_like_this_field');

		$this->addClauseParameters($fuzzyLikeThisField, $parameters);

		$fuzzyLikeThisField->likeText($value);

		$this->setStatement('fuzzy_like_this_field', $field, $fuzzyLikeThisField);

		return $this;
	}

	/**
	 * Fuzzy like this field query alias.
	 *
	 * @param  string 		 $field
	 * @param  mixed 		 $value
	 * @param  Closure|array $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function fltField($field, $value, $parameters = null)
	{
		return $this->fuzzyLikeThisField($field, $value, $parameters);
	}

	/**
	 * Elastic fuzzy query.
	 *
	 * @param  string 	 	 $field
	 * @param  mixed 		 $value
	 * @param  Closure|array $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function fuzzy($field, $value, $parameters = null)
	{
		$fuzzy = $this->newClause('fuzzy');

		$this->addClauseParameters($fuzzy, $parameters);

		$fuzzy->value($value);

		$this->setStatement('fuzzy', $field, $fuzzy);

		return $this;
	}

	/**
	 * Elastic geo shape query.
	 *
	 * @param  string 		 $field
	 * @param  array  		 $coordinates
	 * @param  Closure|array $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function geoShape($field, array $coordinates, $shape = 'shape', $parameters = null)
	{
		$geoShape = $this->newClause('geo_shape');

		if ($shape == 'shape') {
			$geoShape->shape(function($shape) use ($coordinates, $parameters)
			{
				$shape->coordinates($coordinates);
				$shape->type('envelope');

				$this->addClauseParameters($shape, $parameters);
			});
		} elseif ($shape == 'indexed_shape') {
			$geoShape->indexedShape(function($shape) use ($parameters)
			{
				$this->addClauseParameters($shape, $parameters);
			});
		} else {
			throw new \InvalidArgumentException("Invalid shape: [{$shape}]", 1);
		}

		$this->setStatement('geo_shape', $field, $geoShape);

		return $this;
	}

	/**
	 * Elastic has child query.
	 *
	 * @param  Closure $query
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function hasChild(Closure $callback)
	{
		$hasChild = $this->newClause('has_child');

		$callback($hasChild);

		$this->setStatement('has_child', null, $hasChild);

		return $this;
	}

	/**
	 * Elastic has parent query.
	 *
	 * @param  Closure $query
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function hasParent(Closure $callback)
	{
		$hasParent = $this->newClause('has_parent');

		$callback($hasParent);

		$this->setStatement('has_parent', null, $hasParent);

		return $this;
	}

	/**
	 * Elasticsearch ids query.
	 *
	 * @param  string|array|null $type
	 * @param  string|array 	 $values
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function ids($values, $type = null)
	{
		$ids = $this->newClause('ids');

		$ids->values($values);

		if (isset($type)) {
			$ids->type($type);
		}

		$this->setStatement('ids', null, $ids);

		return $this;
	}

	/**
	 * Elasticsearch indices query.
	 *
	 * @param  array  $indices
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function indices(array $indices, Closure $callback)
	{
		$indices = $this->newClause('indices');

		$callback($indices);

		$this->setStatement('indices', null, $indices);

		return $this;
	}

	/**
	 * Elasticsearch match all query.
	 *
	 * @param  array  $indices
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function matchAll($parameters = null)
	{
		$matchAll = $this->newClause('match_all');

		$this->addClauseParameters($matchAll, $parameters);

		$this->setStatement('match_all', null, $matchAll);

		return $this;
	}

	/**
	 * Elasticsearch more like this query.
	 *
	 * @param  array  			  $fields
	 * @param  string 			  $likeText
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function moreLikeThis(array $fields, $likeText, $parameters = null)
	{
		$moreLikeThis = $this->newClause('more_like_this');

		$this->addClauseParameters($moreLikeThis, $parameters);

		$moreLikeThis->fields($fields);

		$moreLikeThis->likeText($likeText);

		$this->setStatement('more_like_this', null, $moreLikeThis);

		return $this;
	}

	/**
	 * Elasticsearch nested query.
	 *
	 * @param  Closure $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function nested(Closure $callback)
	{
		$nested = $this->newClause('nested');

		$callback($nested);

		$this->setStatement('nested', null, $nested);

		return $this;
	}

	/**
	 * Elasticsearch prefix query.
	 *
	 * @param  string $field
	 * @param  mixed $value
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function prefix($field, $value, $parameters = null)
	{
		$prefix = $this->newClause('prefix');

		$this->addClauseParameters($prefix, $parameters);

		$prefix->value($value);

		$this->setStatement('prefix', $field, $prefix);

		return $this;
	}

	/**
	 * Elasticsearch query string query.
	 *
	 * @param  string $query
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function queryString($query, $parameters = null)
	{
		$queryString = $this->newClause('query_string');

		$this->addClauseParameters($queryString, $parameters);

		$queryString->query($query);

		$this->setStatement('query_string', null, $queryString);

		return $this;
	}

	/**
	 * Elasticsearch simple query string query.
	 *
	 * @param  string $query
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function simpleQueryString($query, $parameters = null)
	{
		$simpleQueryString = $this->newClause('simple_query_string');

		$this->addClauseParameters($simpleQueryString, $parameters);

		$simpleQueryString->query($query);

		$this->setStatement('simple_query_string', null, $simpleQueryString);

		return $this;
	}

	/**
	 * Elasticsearch regex query.
	 *
	 * @param  string $field
	 * @param  string $value
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function regex($field, $value, $parameters = null)
	{
		$regex = $this->newClause('regex');

		$this->addClauseParameters($regex, $parameters);

		$regex->value($value);

		$this->setStatement('regex', $field, $regex);

		return $this;
	}

	/**
	 * Elasticsearch terms query.
	 *
	 * @param  string $field
	 * @param  array  $values
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function terms($field, array $values, $parameters = null)
	{
		$terms = $this->newClause('terms');

		$this->addClauseParameters($terms, $parameters);

		//Add the values
		$terms->$field($values);

		$this->setStatement('terms', null, $terms);

		return $this;
	}

	/**
	 * Elastic range query.
	 *
	 * @param  string $field
	 * @param  Closure|array $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function range($field, $parameters)
	{
		$range = $this->newClause('range');

		$this->addClauseParameters($range, $parameters);

		$this->setStatement('range', $field, $range);

		return $this;
	}

	/**
	 * Elastic term query.
	 *
	 * @param  string       $field
	 * @param  array        $value
	 * @param  Closure|null $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function term($field, $value, $parameters = null)
	{
		$term = $this->newClause('term');

		$this->addClauseParameters($term, $parameters);

		$term->value($value);

		$this->setStatement('term', $field, $term);

		return $this;
	}

	/**
	 * Elastic wildcard query.
	 *
	 * @param  string       $field
	 * @param  mixed        $value
	 * @param  Closure|null $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function wildcard($field, $value, $parameters = null)
	{
		$wildcard = $this->newClause('wildcard');

		$this->addClauseParameters($wildcard, $parameters);

		$wildcard->value($value);

		$this->setStatement('wildcard', $field, $wildcard);

		return $this;
	}

	/**
	 * Insert a raw query into the builder.
	 *
	 * @param  string $json
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function raw($json)
	{
		$this->setStatement('raw', null, $json);

		return $this;
	}

	/**
	 * Make a new clause.
	 *
	 * @param  array|string $name
	 * @return \Tamayo\Stretchy\Query\Clause\Clause
	 */
	protected function newClause($name)
	{
		return $this->clauseFactory->make($name, $this);
	}

	/**
	 * Add a parameters to a clause.
	 *
	 * @param \Tamayo\Stretchy\Search\Clause\Clause $clause
	 * @param Closure|array 						 $parameters
	 * @return \Tamayo\Stretchy\Search\Clause\Clause
	 */
	protected function addClauseParameters(&$clause, $parameters)
	{
		if ($parameters instanceof Closure) {
			$parameters($clause);
		}
		elseif (is_array($parameters)) {
			$clause->addRawConstraints($parameters);
		}

		return $clause;
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
		} else {
			$container = Str::camel($type);

			$this->$container = isset($this->$container) ? $this->$container : array();

			$this->$container = array_merge($this->$container, [['field' => $field, 'value' => $value]]);

			if (! in_array($type, $this->statements)) {
				$this->statements[] = $type;
			}
		}
	}

	/**
	 * Get the statements set by the builder.
	 *
	 * @return array
	 */
	public function getStatements()
	{
		return $this->statements;
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
		return new static($this->connection, $this->grammar, $this->processor, $this->clauseFactory);
	}

}
