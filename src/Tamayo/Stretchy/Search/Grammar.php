<?php namespace Tamayo\Stretchy\Search;

use Illuminate\Support\Str;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Clauses\Clause;
use Tamayo\Stretchy\Grammar as BaseGrammar;


class Grammar extends BaseGrammar {

	/**
	 * Compile the search query.
	 *
	 * @param  \Tamayo\Stretchy\Search\Builder $builder
	 * @return array
	 */
	public function compileSearch(Builder $builder)
	{

		$header = $this->compileHeader($builder);

		$singleStatement = $builder->getSingleStatement();

		if (isset($singleStatement)) {
			$body = $this->compileSingleStatement($singleStatement);
		}
		else
		{
			$body = $this->compileSubqueries($builder);
		}

		if ($builder->isSubquery()) {
			return $body;
		}

		return array_merge($header, ['body' => $body]);
	}

	/**
	 * Compile a match all statement.
	 *
	 * @param  array $matchAll
	 * @return array
	 */
	public function compileMatchAll($matchAll)
	{
		$compiled = $this->compileClause($matchAll['value']);

		$compiled = isset($compiled)? $compiled : new \StdClass;

		return $this->compile('match_all', $compiled);
	}

	/**
	 * Compile single statement.
	 *
	 * @param  array $statement
	 * @return array
	 */
	protected function compileSingleStatement($statement)
	{
		$compiled = array();

		return $this->compile('query', $this->callCompileMethod($statement['type'], $statement));
	}

	/**
	 * Compile a default statement.
	 *
	 * @param  array $statement
	 * @return array
	 */
	public function compileDefaultStatement($type, $statement)
	{
		if (! isset($statement['field'])) {
			$compiled = $this->compileClause($statement['value']);
		}
		else {
			$compiled = $this->compile($statement['field'], $this->compileClause($statement['value']));
		}

		return $this->compile($type, $compiled);
	}

	/**
	 * Compile the a clause with its contraints.
	 *
	 * @param  \Tamayo\Stretchy\Search\Clauses\Clause $clause
	 * @param  array|null $container
	 * @return array
	 */
	protected function compileClause(Clause $clause)
	{
		$compiled = $clause->toArray();

		if (empty($compiled)) {
			return null;
		}

		return $compiled;
	}

	/**
	 * Compile a set of subqueries statements.
	 *
	 * @param  array  $queries
	 * @return array
	 */
	protected function compileSubqueries(Builder $builder)
	{
		$compiled = array();

		foreach ($builder->getStatements() as $query) {
			$compiled = array_merge_recursive($compiled, $this->compileSubquery($builder, $query));
		}

		if(count($compiled) == 1){
			$compiled = array_shift($compiled);
		}

		return $compiled;
	}

	/**
	 * Compile a single subquery statements.
	 *
	 * @param  string $name
	 * @return array
	 */
	protected function compileSubquery($builder, $name)
	{
		$compiled  = array();
		$container = Str::camel($name);

		if (isset($builder->$container)) {
			foreach ($builder->$container as $statement) {
				$compiled[] = $this->callCompileMethod($container, $statement);
			}
		}

		return $compiled;
	}

	/**
	 * Check if method exists in class.
	 *
	 * @param  string $name
	 * @return bool
	 */
	protected function methodExists($name)
	{
		return method_exists($this, $name);
	}

	/**
	 * Call the compile method of the statement.
	 *
	 * @param  string $type
	 * @param  mixed  $statement
	 * @return array
	 */
	protected function callCompileMethod($type, $statement)
	{
		$method = 'compile'.ucfirst($type);

		if ($this->methodExists($method)) {
			return $this->$method($statement);
		}

		//If method does not exists, asume as default compilation
		return $this->compileDefaultStatement(Str::snake($type), $statement);
	}
}
