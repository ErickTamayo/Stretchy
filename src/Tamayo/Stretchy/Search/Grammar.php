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
			$body = $this->compileSubqueries($builder, [
				'match', 'multi_match', 'bool', 'boosting', 'common', 'term', 'constant_score', 'dis_max', 'filtered', 'range'
			]);
		}

		if ($builder->isSubquery()) {
			return $body;
		}

		return array_merge($header, ['body' => $body]);
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

		$method = 'compile'.ucfirst($statement['type']);

		return $this->compile('query', $this->$method($statement));
	}

	/**
	 * Compile a match statement.
	 *
	 * @param  array $match
	 * @return array
	 */
	protected function compileMatch($match)
	{
		$subCompiled = $this->compile($match['field'], $this->compileClause($match['value']));

		return $this->compile('match', $subCompiled);
	}

	/**
	 * Compile a multi match statement.
	 *
	 * @param  array $multiMatch
	 * @return array
	 */
	protected function compileMultiMatch($multiMatch)
	{
		return $this->compile('multi_match', $this->compileClause($multiMatch['value']));
	}

	/**
	 * Compile a bool statement.
	 *
	 * @param  array $bool
	 * @return array
	 */
	protected function compileBool($bool)
	{
		$compiled = $this->compileClause($bool['value'], ['must', 'mustNot', 'should']);

		return $this->compile('bool', $compiled);
	}

	/**
	 * Compile a boosting statement.
	 *
	 * @param  array $boosting
	 * @return array
	 */
	protected function compileBoosting($boosting)
	{
		$compiled = $this->compileClause($boosting['value'], ['positive', 'negative']);

		return $this->compile('boosting', $compiled);
	}

	/**
	 * Compile a common statement.
	 *
	 * @param  array $common
	 * @return array
	 */
	protected function compileCommon($common)
	{
		$compiled = $this->compile($common['field'], $this->compileClause($common['value'], ['minimumShouldMatch']));

		return $this->compile('common', $compiled);
	}

	/**
	 * Compile a constant score statement.
	 *
	 * @param  array $constantScore
	 * @return array
	 */
	protected function compileConstantScore($constantScore)
	{
		$compiled = $this->compileClause($constantScore['value'], ['filter', 'query']);

		return $this->compile('constant_score', $compiled);
	}

	/**
	 * Compile a dis max statement.
	 *
	 * @param  array $disMax
	 * @return array
	 */
	protected function compileDisMax($disMax)
	{
		$compiled = $this->compileClause($disMax['value'], ['queries']);

		return $this->compile('dis_max', $compiled);
	}

	/**
	 * Compile a filtered statement.
	 *
	 * @param  array $filtered
	 * @return array
	 */
	protected function compileFiltered($filtered)
	{
		$compiled = $this->compileClause($filtered['value'], ['query', 'filter']);

		return $this->compile('filtered', $compiled);
	}

	/**
	 * Compile a range statement.
	 *
	 * @param  array $term
	 * @return array
	 */
	protected function compileRange($range)
	{
		$compiled = $this->compile($range['field'], $this->compileClause($range['value']));

		return $this->compile('range', $compiled);
	}

	/**
	 * Compile a term statement.
	 *
	 * @param  array $term
	 * @return array
	 */
	protected function compileTerm($term)
	{
		$compiled = $this->compile($term['field'], $this->compileClause($term['value']));

		return $this->compile('term', $compiled);
	}

	/**
	 * Compile the a clause with its contraints.
	 *
	 * @param  \Tamayo\Stretchy\Search\Clauses\Clause $clause
	 * @param  array|null $container
	 * @return array
	 */
	protected function compileClause(Clause $clause, $subClauses = array())
	{
		$compiled = $clause->getAffectedConstraints();

		foreach ($subClauses as $subClause) {
			if (isset($clause->$subClause)) {
				$compiled = array_merge($this->compile(Str::snake($subClause), $clause->$subClause->toArray()), $compiled);
			}
		}

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
	protected function compileSubqueries(Builder $builder, array $queries)
	{
		$compiled = array();

		foreach ($queries as $query) {
			$compiled = array_merge_recursive($compiled, $this->compileSubquery($builder, $query));
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

			$method = 'compile'.ucfirst($container);

			foreach ($builder->$container as $query) {
				$compiled[] = $this->$method($query);
			}
		}

		return $compiled;
	}
}
