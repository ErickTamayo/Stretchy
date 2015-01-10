<?php namespace Tamayo\Stretchy\Search;

use Closure;
use Illuminate\Support\Str;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Processor;
use Tamayo\Stretchy\Search\Clauses\Clause;
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
	 * @param  string  			  $field
	 * @param  mixed  			  $value
	 * @param  Closure|array|null $parameters
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function match($field, $matching, $parameters = null, $type = 'boolean')
	{
		$match = new Clause($this);

		$match->setConstraints(['query', 'fields', 'type', 'tie_breaker', 'analyzer', 'boost', 'operator', 'minimum_should_match', 'fuzziness', 'prefix_length', 'max_expansions', 'rewrite', 'zero_terms_query', 'cutoff_frequency', 'lenient']);

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
		$match = new Clause($this);

		$match->setConstraints(['query', 'fields', 'type', 'tie_breaker', 'analyzer', 'boost', 'operator', 'minimum_should_match', 'fuzziness', 'prefix_length', 'max_expansions', 'rewrite', 'zero_terms_query', 'cutoff_frequency']);

		$this->addClauseParameters($match, $parameters);

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
		$bool = new \Tamayo\Stretchy\Search\Clauses\Bool($this);

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
		$boosting = new \Tamayo\Stretchy\Search\Clauses\Boosting($this);

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
		$common = new \Tamayo\Stretchy\Search\Clauses\Common($this);

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
		$constantScore = new \Tamayo\Stretchy\Search\Clauses\ConstantScore($this);

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
		$disMax = new \Tamayo\Stretchy\Search\Clauses\DisMax($this);

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
		$filtered = new \Tamayo\Stretchy\Search\Clauses\Filtered($this);

		$callback($filtered);

		$this->setStatement('filtered', null, $filtered);

		return $this;
	}

	public function fuzzyLikeThis(array $fields, $value, $parameters = null)
	{
		$fuzzyLikeThis = new Clause($this);

		$fuzzyLikeThis->setConstraints(['fields', 'like_text', 'ignore_tf', 'max_query_terms', 'fuzziness', 'prefix_length', 'boost', 'analyzer']);

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
		$fuzzyLikeThisField = new Clause($this);

		$fuzzyLikeThisField->setConstraints(['like_text', 'ignore_tf', 'max_query_terms', 'fuzziness', 'prefix_length', 'boost', 'analyzer']);

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
		$fuzzy = new Clause($this);

		$fuzzy->setConstraints(['value', 'boost', 'fuzziness', 'prefix_length', 'max_expansions']);

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
		$geoShape = new \Tamayo\Stretchy\Search\Clauses\GeoShape($this);

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
		$hasChild = new \Tamayo\Stretchy\Search\Clauses\HasChild($this);

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
		$hasParent = new \Tamayo\Stretchy\Search\Clauses\HasParent($this);

		$callback($hasParent);

		$this->setStatement('has_parent', null, $hasParent);

		return $this;
	}

	/**
	 * Elasticsearch ids query.
	 *
	 * @param  string|array|null $type
	 * @param  string|array 	 $values
	 * @return  \Tamayo\Stretchy\Search\Builder
	 */
	public function ids($values, $type = null)
	{
		$ids = new \Tamayo\Stretchy\Search\Clauses\Ids($this);

		$ids->values($values);

		if (isset($type)) {
			$ids->type($type);
		}

		$this->setStatement('ids', null, $ids);

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
		$range = new Clause($this);

		$range->setConstraints(['gte', 'gt', 'lte', 'lt', 'boost', 'time_zone']);

		$this->addClauseParameters($range, $parameters);

		$this->setStatement('range', $field, $range);

		return $this;
	}

	/**
	 * Elastic term query.
	 *
	 * @param  string       $field
	 * @param  mixed        $value
	 * @param  Closure|null $callback
	 * @return \Tamayo\Stretchy\Search\Builder
	 */
	public function term($field, $value, $parameters = null)
	{
		$term = new Clause($this);

		$term->setConstraints(['boost', 'value']);

		$this->addClauseParameters($term, $parameters);

		$term->value($value);

		$this->setStatement('term', $field, $term);

		return $this;
	}

	/**
	 * Add a parameters to a clause.
	 *
	 * @param \Tamayo\Stretchy\Search\Clauses\Clause $clause
	 * @param Closure|array 						 $parameters
	 * @return Tamayo\Stretchy\Search\Clauses\Clause
	 */
	protected function addClauseParameters(Clause &$clause, $parameters)
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
		return new static($this->connection, $this->grammar, $this->processor);
	}

}
