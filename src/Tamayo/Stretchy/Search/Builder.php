<?php

namespace Tamayo\Stretchy\Search;

use Closure;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Support\Str;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Processor;
use Tamayo\Stretchy\Search\Parameter;
use Tamayo\Stretchy\Builder as BaseBuilder;
use Tamayo\Stretchy\Exceptions\QueryNotSupportedException;

class Builder extends BaseBuilder {

    /**
     * Query Processor.
     *
     * @var \Tamayo\Stretchy\Search\Processor
     */
    protected $processor;

    /**
     * The clause factory instance.
     *
     * @var \Tamayo\Stretchy\Search\Clause\Factory
     */
    protected $clauseFactory;

    /**
     * Indicates if this builder is a nested instance.
     *
     * @var boolean
     */
    protected $nested = false;

    /**
     * Indicates if this builder queries should be associative or not.
     *
     * @var boolean
     */
    protected $associative = false;

    /**
     * Indices to perform the search.
     *
     * @var array
     */
    public $indices;

    /**
     * Raw query.
     *
     * @var array|string
     */
    public $raw = [];

    /**
     * Query constraints.
     *
     * @var array
     */
    public $queries = [];

    /**
     * Filter constraints.
     *
     * @var array
     */
    public $filters = [];

    /**
     * Create a new search builder.
     *
     * @param \Tamayo\Stretchy\Connection $connection
     * @param \Tamayo\Stretchy\Search\Grammar $grammar
     * @param \Tamayo\Stretchy\Search\Processor $processor
     */
    public function __construct(Connection $connection, Grammar $grammar, Processor $processor)
    {
        parent::__construct($connection, $grammar);
        $this->processor = $processor;
    }

    /**
     * Checks if this is a nested query.
     *
     * @return boolean
     */
    public function isNested()
    {
        return $this->nested;
    }

    /**
     * Declares the builder as a nested subquery.
     *
     * @param boolean $associative
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function asNested($associative)
    {
        $this->nested = true;
        $this->associative = $associative;
        return $this;
    }

    /**
     * Determines if the current query is single or not.
     *
     * @return boolean
     */
    public function isSingle()
    {
        $count = array_reduce($this->queries, function ($carry, $statement) {
            return $carry += sizeof($statement);
        });

        return $count <= 1;
    }

    /**
     * Determines if the current query is associative or not.
     *
     * @return boolean
     */
    public function isAssociative()
    {
        return $this->associative;
    }

    /**
     * Index alias.
     *
     * @param  string  $index
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function search($indices = [])
    {
        $this->indices = (array) $indices;
        return $this;
    }

    /**
     * Add a simple key:value statement.
     *
     * @param  string $name
     * @param  array $arguments
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function __call($name, $arguments)
    {
        return $this->addQuery(Str::snake($name), ['value' => isset($arguments[0]) ? $arguments[0] : null ]);
    }

    /**
     * Match Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function match($field, $value, array $parameters = [], $type = null)
    {
        if ($type) {
            $parameters = array_merge($parameters, ['type' => $type]);
        }

        return $this->addQuery('match', compact('field', 'value', 'parameters'));
    }

    /**
     * match phrase query.
     *
     * @param  string       $field
     * @param  mixed        $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function matchPhrase($field, $value, $parameters = [])
    {
        return $this->match($field, $value, $parameters, 'phrase');
    }

    /**
     * match phrase prefix query.
     *
     * @param  string       $field
     * @param  mixed        $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function matchPhrasePrefix($field, $value, $parameters = [])
    {
        return $this->match($field, $value, $parameters, 'phrase_prefix');
    }

    /**
     * multi match query.
     *
     * @param  array              $field
     * @param  string             $value
     * @param  array|null         $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function multiMatch(array $field, $value, $parameters = [])
    {
        return $this->addQuery('multi_match', compact('field', 'value', 'parameters'));
    }

    /**
     * bool query.
     *
     * @param  Closure $callback
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function bool(Closure $callback)
    {
        return $this->addQuery('bool', ['subquery' => $callback], true);
    }

    /**
     * Add a should clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function should($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('should', $clause, $field, $value, $parameters);
    }

    /**
     * Add a must clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function must($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('must', $clause, $field, $value, $parameters);
    }

    /**
     * Add a must not clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function mustNot($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('must_not', $clause, $field, $value, $parameters);
    }

    /**
     * boosting query.
     *
     * @param  Closure $callback
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function boosting(Closure $callback)
    {
        return $this->addQuery('boosting', ['subquery' => $callback], true);
    }

    /**
     * Add a positive clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function positive($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('positive', $clause, $field, $value, $parameters);
    }

    /**
     * Add a negative clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function negative($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('negative', $clause, $field, $value, $parameters);
    }

    /**
     * term Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function term($field, $value, $parameters = [])
    {
        return $this->addQuery('term', compact('field', 'value', 'parameters'));
    }

    /**
     * common Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function common($field, $value, array $parameters = [])
    {
        return $this->addQuery('common', compact('field', 'value', 'parameters'));
    }

    /**
     * constant score query.
     *
     * @param  Closure $callback
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function constantScore(Closure $callback)
    {
        return $this->addQuery('constant_score', ['subquery' => $callback], true);
    }

    /**
     * dis max score query.
     *
     * @param  Closure $callback
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function disMax(Closure $callback)
    {
        return $this->addQuery('dis_max', ['subquery' => $callback], true);
    }

    /**
     * Add a queries clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function queries($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('queries', $clause, $field, $value, $parameters);
    }

    /**
     * fuzzy Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function fuzzy($field, $value, $parameters = [])
    {
        return $this->addQuery('fuzzy', array_merge(compact('field', 'value', 'parameters'), ['query_field_name' => 'value']), false);
    }

    /**
     * match all query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function matchAll($parameters = [])
    {
        return $this->addQuery('match_all', ['value' => [], 'parameters' => $parameters]);
    }

    /**
     * geo shape Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function geoShape($field, $lon1, $lat1, $lon2, $lat2, $relation = 'within')
    {
        $parameters = ['field' => $field, 'value' => [[$lon1, $lat1], [$lon2, $lat2]], 'parameters' => ['relation' => $relation ]];

        return $this->bool(function($query) use ($field, $parameters) {
            $query->must('match_all');
            $query->filter(function($query) use ($parameters) {
                $query->addQuery('geo_shape', $parameters);
            });
        });
    }

    /**
     * geo shape Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function preindexedGeoShape($field, $id, $type, $index, $path)
    {
        $parameters = ['field' => $field, 'value' => null, 'parameters' => [
                'id'             => $id,
                'type'           => $type,
                'index'          => $index,
                'path'           => $path,
                'indexed_shape'  => true
            ]
        ];

        return $this->bool(function($query) use ($field, $parameters) {
            $query->must('match_all');
            $query->filter(function($query) use ($parameters) {
                $query->addQuery('geo_shape', $parameters);
            });
        });
    }

    /**
     *  has child query.
     *
     * @param  string  $type
     * @param  mixed  $clause
     * @param  string  $field
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function hasChild($type, $clause, $field = null, $value = null, $parameters = [])
    {
        if (! $clause instanceof Closure) {
            $clause = $this->newSearchNestedQuery()->addNamedNestedQuery('query', $clause, $field, $value, $parameters);
        }

        return $this->addQuery('has_child', ['parameters' => ['type' => $type], 'subquery' => $clause], true);
    }

    /**
     *  has parent query.
     *
     * @param  string  $type
     * @param  mixed  $clause
     * @param  string  $field
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function hasParent($type, $clause, $field = null, $value = null, $parameters = [])
    {
        if (! $clause instanceof Closure) {
            $clause = $this->newSearchNestedQuery()->addNamedNestedQuery('query', $clause, $field, $value, $parameters);
        }

        $parameters['parent_type'] = $type;

        return $this->addQuery('has_parent', ['subquery' => $clause, 'parameters' => $parameters], true);
    }

    /**
     * Add a query clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function query($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('query', $clause, $field, $value, $parameters);
    }

    /**
     * Add a a no match query clause to the search query.
     *
     * @param Closure|String $clause
     * @param String $field
     * @param mixed $value
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function noMatchQuery($clause, $field = null, $value = null, $parameters = [])
    {
        return $this->addNamedNestedQuery('no_match_query', $clause, $field, $value, $parameters);
    }

    /**
     * indices query
     * @param  array $indices
     * @param  Closure $query
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function indices(array $indices, Closure $clause)
    {
        $this->newSearchNestedQuery()->addNamedNestedQuery('indices', $clause, null, null, null);

        return $this->addQuery('indices', ['subquery' => $clause, 'parameters' => ['indices' => $indices]], true);
    }

    /**
     *  ids query.
     *
     * @param  string  $type
     * @param  mixed  $clause
     * @param  string  $field
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function ids(array $ids, $type = null)
    {
        $parameters = ['values' => $ids];

        $parameters = isset($type) ? array_merge($parameters, ['type' => $type]) : $parameters;

        return $this->addQuery('ids', ['parameters' => $parameters]);
    }

    /**
     * more like this query.
     *
     * @param  array  $fields
     * @param  string|Closure  $like
     * @param  array  $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function moreLikeThis(array $fields, $like, $parameters = [])
    {
        if ($like instanceof Closure) {
            $parameters = ['parameters' => array_merge(['fields' => $fields], $parameters), 'subquery' => $like];
        } else {
            $parameters = ['parameters' => array_merge(['fields' => $fields, 'like' => $like], $parameters)];
        }

        return $this->addQuery('more_like_this', $parameters, true);
    }

    /**
     * Add a like or unlike clause to the search query.
     *
     * @param  string $index
     * @param  string $type
     * @param  string|array $idOrDoc
     * @param string $like
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function likeOrUnlike($index, $type = null, $idOrDoc = null, $like = 'like')
    {
        if ($type == null && $idOrDoc == null) {
            $value = $index;
        } elseif (is_array($idOrDoc)) {
            $value =  ['_index' => $index, '_type' => $type, 'doc' => $idOrDoc];
        } else {
            $value =  ['_index' => $index, '_type' => $type, '_id' => $idOrDoc];
        }
        return $this->addQuery($like, ['value' => $value ]);
    }

    /**
     * Add a like clause to the search query.
     *
     * @param  string $index
     * @param  string $type
     * @param  string|array $idOrDoc
     * @param string $like
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function like($index, $type = null, $idOrDoc = null, $like = 'like') {
        return $this->likeOrUnlike($index, $type, $idOrDoc);
    }

    /**
     * Add a like or unlike clause to the search query.
     *
     * @param  string $index
     * @param  string $type
     * @param  string|array $idOrDoc
     * @param string $like
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function unlike($index, $type = null, $idOrDoc = null, $like = 'like') {
        return $this->likeOrUnlike($index, $type, $idOrDoc, 'unlike');
    }

    /**
     * Nested query.
     *
     * @param  string  $path
     * @param  string  $scoreMode
     * @param  Closure $callback
     * @return \Tamayo\Strethy\Search\builder
     */
    public function nested($path, $scoreMode, Closure $callback)
    {
        return $this->addQuery('nested', ['subquery' => $callback, 'parameters' => ['path' => $path, 'score_mode' => $scoreMode]], true);
    }

    /**
     * Add a bool type nested statement to the builder.
     *
     * @param string $type
     * @param string $clause
     * @param string $field
     * @param mixed $value
     * @param array|null $parameters
     */
    public function addNamedNestedQuery($name, $clause, $field = null, $value = null, $parameters = [])
    {
        $subquery = $this->newSearchNestedQuery();

        // If is a closure we asume the developer wants to add more than one
        // statement to the clause
        if ($clause instanceof Closure) {
            $clause($subquery);
            return $this->addQuery($name, ['field' => $field, 'value' => $value, 'parameters' => $parameters, 'subquery' => $subquery]);
        } else {
            call_user_func_array([$subquery, $clause], [$field, $value, $parameters]);
            return $this->addQuery($name, ['subquery' => $subquery]);
        }
    }

    /**
     * Prefix Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function prefix($field, $value, $parameters = [])
    {
        return $this->addQuery('prefix', ['field' => $field, 'value' => $value, 'parameters' => $parameters, 'query_field_name' => 'prefix']);
    }

    /**
     * Regexp Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function regexp($field, $value, $parameters = [])
    {
        return $this->addQuery('regexp', ['field' => $field, 'value' => $value, 'parameters' => $parameters, 'query_field_name' => 'value']);
    }

    /**
     * Range Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function range($field, $value)
    {
        return $this->addQuery('range', ['field' => $field, 'value' => $value, 'parameters' => []]);
    }

    /**
     * Filter compound query.
     *
     * @todo Fix this to compile corretly as a filter, see geo shape query
     * @param  Closure $callback
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function filter(Closure $callback)
    {
        return $this->addQuery('filter', ['subquery' => $callback], true);
    }

    /**
     * Terms Query.
     *
     * @param  string             $field
     * @param  mixed              $value
     * @param  array|null $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function terms($field, $value)
    {
        return $this->addQuery('terms', compact('field', 'value'));
    }

    /**
     * query_string Query.
     *
     * @param  mixed $defaultFieldOrFields
     * @param  string $query
     * @param  array $parameters
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function queryString($defaultFieldOrFields, $query, array $parameters = [])
    {
        $queryFieldName = is_array($defaultFieldOrFields) ? 'fields' : 'default_field';

        $parameters = array_merge($parameters, [$queryFieldName => $defaultFieldOrFields, 'query' => $query ]);

        return $this->addQuery('query_string', ['field' => null, 'value' => null, 'parameters' => $parameters]);
    }

    /**
     * Add a query statement to the Builder.
     *
     * @param string $type
     * @param array $parameters
     * @param boolean $associative
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function addQuery($type, $parameters = [], $associative = false, $append = false)
    {
        if(! Parameter::isValid($type)) {
            throw new QueryNotSupportedException("Query [{$type}] is not supported", 1);
        }

        // Check if subquery is a closure to convert it to a real subquery
        if (isset($parameters['subquery']) and $parameters['subquery'] instanceof Closure) {
            $subquery = $this->newSearchNestedQuery($associative);
            call_user_func($parameters['subquery'], $subquery);
            $parameters['subquery'] = $subquery;
        }

        $this->queries[$type][] = $parameters;

        return $this;
    }

    /**
     * Get a new instance of the Builder.
     *
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function newSearchQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Get a new instance of the Builder and set as nested.
     *
     * @param boolean $associative
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function newSearchNestedQuery($associative = false)
    {
        return $this->newSearchQuery()->asNested($associative);
    }

    /**
     * Insert a raw query into the builder.
     *
     * @param  array|string $raw
     * @return \Tamayo\Stretchy\Search\Builder
     */
    public function raw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Execute the search and return the first.
     *
     * @return array
     */
    public function first()
    {
        return $this->size(1)->get();
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
