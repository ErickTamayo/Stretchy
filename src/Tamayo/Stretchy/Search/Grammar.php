<?php

namespace Tamayo\Stretchy\Search;

use Closure;
use Tamayo\Stretchy\Support\Str;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Clauses\Clause;
use Tamayo\Stretchy\Grammar as BaseGrammar;
use Tamayo\Stretchy\Exceptions\GrammarException;

class Grammar extends BaseGrammar {

    /**
     * Clauses that are array.
     *
     * @var array
     */
    protected $arrayClauses = ['like', 'unlike'];

    /**
     * Compile the query.
     *
     * @param  \Tamayo\Stretchy\Query\Search $builder
     * @return array
     */
    public function compileSearch(Builder $builder)
    {
        $compiled = array_merge_recursive(
            $this->compileBuilderIndices($builder),
            $this->compileBody($builder)
        );

        return $compiled;
    }

    /**
     * Compile the body of the query.
     *
     * @param  Builder $builder
     * @return array
     */
    public function compileBody(Builder $builder)
    {
        $body = array_merge_recursive(
            $this->compileBuilderSize($builder),
            $this->compileBuilderFrom($builder),
            $this->compileBuilderQueries($builder),
            $this->compileBuilderRaw($builder)
        );

        return sizeof($body) == 0 ? [] : compact('body');
    }

    /**
     * Compile size
     *
     * @param  Builder $builder
     * @return array
     */
    public function compileBuilderSize(Builder $builder)
    {
        return isset($builder->size) ? ['size' => $builder->size] : [];
    }

    /**
     * Compile from
     *
     * @param  Builder $builder
     * @return array
     */
    public function compileBuilderFrom(Builder $builder)
    {
        return isset($builder->from) ? ['from' => $builder->from] : [];
    }

    /**
     * Compile raw statement
     *
     * @param  Builder $builder
     * @return array
     */
    public function compileBuilderRaw(Builder $builder)
    {
        if (is_string($builder->raw)) {
            $compiled = json_decode($builder->raw, true);

            $error = json_last_error();
            if ($error !== JSON_ERROR_NONE) {
                throw new GrammarException("Unable to parse json", 1);
            }

            return $compiled;
        }

        return $builder->raw;
    }

    /**
     * Compile statements under the 'query' field.
     *
     * @param  Builder $builder
     * @return array
     */
    public function compileBuilderQueries(Builder $builder)
    {
        $query = [];

        // The search 'query' field must be single, so we take always the
        // first occurence that we encounter
        foreach ($builder->queries as $type => $statements) {
            foreach ($statements as $statement) {
                // If the query is single we just assign the compiled to the
                // query variable
                if ($builder->isSingle()) {
                    $query[$type] = $this->compile($type, $statement);
                }
                // If the query is not single but is associative we must check if
                // the query has already a key of the same type and push, if so
                // then we must compile and merge under the same type to prevent
                // overriding the same type in this query level.
                else if($builder->isAssociative()){
                    if (!isset($query[$type])) { $query[$type] = []; }

                    $compiled = $this->compile($type, $statement);
                    // If is an array can be merged, otherwise assign the value
                    // to the current query (we can't prevent overriding at this
                    // point)
                    if (in_array($type, $this->arrayClauses)) {
                        $query[$type][] = $this->compile($type, $statement);
                    }
                    elseif (is_array($compiled)) {
                        $query[$type] = array_merge($query[$type], $compiled);
                    }
                    else {
                        $query[$type] = $compiled;
                    }
                } elseif (in_array($type, $this->arrayClauses)) {
                    $query[$type][] = $this->compile($type, $statement);
                }
                // If is not associative, assign the type and push as a common array
                else {
                    $query[] = [$type => $this->compile($type, $statement)];
                }
            }
        }

        if ($builder->isNested()) {
            return $query;
        }

        return sizeof($query) != 0 ? compact('query') : [];
    }

    /**
     * Compiles the value into an array with the key if the statement exists.
     *
     * @param  string $type
     * @param  array $statement
     * @param  mixed $class
     * @return array
     */
    public function compile($type, $statement, $class = null)
    {
        $compiled = [];

        // If theres a special case to be handle on its own compile method
        $method =  'compile'.Str::studly($type);

        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $statement, $class);
        }

        $statement = $this->sanitizeStatement($statement);

        // Importing sanitized statement array as variables:
        // $field, $values, $parameters, $queryFieldName, $subquery
        extract($statement);

        // compile subquery
        $compiled = isset($subquery) ? array_merge($compiled, $this->compileBuilderQueries($subquery)) : $compiled;

        // compile parameters
        if (isset($parameters) && count($parameters)) {

            $queryFieldName = isset($queryFieldName) ? $queryFieldName : 'query';

            $value = isset($value) ? [ $queryFieldName => $value ] : [];

            if (isset($field)) {
                $compiled = array_merge([$field => array_merge($value, $parameters)], $compiled);
            } else {
                $compiled = array_merge($parameters, $value, $compiled);
            }

        } elseif (isset($field)) {
            $compiled = array_merge([$field => $value], $compiled);
        } elseif (isset($value)) {
            $compiled = $value;
        }

        return $compiled;
    }

    /**
     * Filter the statement array.
     *
     * @param  array $array
     * @return array
     */
    public function sanitizeStatement($statement)
    {
        $matchedKeys = array_filter(array_keys($statement), function($key) {
            return in_array($key, ['field', 'value', 'query_field_name', 'parameters', 'subquery']);
        });

        $statement = array_intersect_key($statement, array_flip($matchedKeys));

        foreach ($statement as $key => $value) {
            unset($statement[$key]);
            $statement[Str::camel($key)] = $value;
        }

        return $statement;
    }

    /**
     * Compile match statement.
     *
     * @param  array $statement
     * @param  Builder $builder
     * @return array
     */
    protected function compileMultiMatch($statement, $builder)
    {
        $multiMatch = [ 'query' => $statement['value'], 'fields' => $statement['field'] ];

        return array_merge($multiMatch, (array) $statement['parameters']);
    }

    /**
     * Compile match all statement.
     *
     * @param  array $statement
     * @param  Builder $builder
     * @return array
     */
    protected function compileMatchAll($statement, $builder)
    {
        if (isset($statement['parameters']) && count($statement['parameters'])) {
            return $statement['parameters'];
        }

        return new \StdClass();
    }

    /**
     * Compile geo shape statement.
     *
     * @param  array $statement
     * @param  Builder $builder
     * @return array
     */
    protected function compileGeoShape($statement, $builder)
    {
        if (isset($statement['parameters']['indexed_shape'])) {
            unset($statement['parameters']['indexed_shape']);
            return [
                $statement['field'] => [
                    'indexed_shape' => $statement['parameters']
                ]
            ];
        } else {
            return [
                $statement['field'] => [
                    'shape' => [
                        'type' => 'envelope',
                        'coordinates' => $statement['value']
                    ],
                    'relation' => $statement['parameters']['relation']
                ]
            ];
        }
    }

    /**
     * Compile a raw statement.
     *
     * @param  array $raw
     * @return array
     */
    public function compileRaw($raw)
    {
        if (is_array($raw['value'])) {
            return $raw['value'];
        }

        return json_decode($raw['value']);
    }
}
