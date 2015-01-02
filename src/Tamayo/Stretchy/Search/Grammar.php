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
			$body = array_merge_recursive(
				$this->compileMatches($builder),
				$this->compileBools($builder),
				$this->compileBoostings($builder),
				$this->compileCommons($builder)
			);
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
	 * Compile the match statements.
	 *
	 * @param  \Tamayo\Stretchy\Search\Builder $builder
	 * @return array
	 */
	protected function compileMatches(Builder $builder)
	{
		$compiled = array();

		foreach ($builder->match as $match) {
			$compiled[] = $this->compileMatch($match);
		}

		return $compiled;
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
	public function compileMultiMatch($multiMatch)
	{
		return $this->compile('multi_match', $this->compileClause($multiMatch['value']));
	}

	/**
	 * Compile boolean statements.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileBools(Builder $builder)
	{
		$compiled = array();

		foreach ($builder->bool as $bool) {
			$compiled[] = $this->compileBool($bool);
		}

		return $compiled;
	}

	/**
	 * Compile a bool statement.
	 *
	 * @param  array $bool
	 * @return array
	 */
	public function compileBool($bool)
	{
		$subClauses = ['must', 'mustNot', 'should'];

		$compiled = $this->compileClause($bool['value'], $subClauses);

		return $this->compile('bool', $compiled);
	}

	/**
	 * Compile boosting statements.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileBoostings(Builder $builder)
	{
		$compiled = array();

		foreach ($builder->boosting as $boosting) {
			$compiled[] = $this->compileBoosting($boosting);
		}

		return $compiled;
	}

	/**
	 * Compile a boosting statement.
	 *
	 * @param  array $boosting
	 * @return array
	 */
	public function compileBoosting($boosting)
	{
		$subClauses = ['positive', 'negative'];

		$compiled = $this->compileClause($boosting['value'], $subClauses);

		return $this->compile('boosting', $compiled);
	}

	/**
	 * Compile common statements.
	 *
	 * @param  Builder $builder
	 * @return array
	 */
	public function compileCommons(Builder $builder)
	{
		$compiled = array();

		foreach ($builder->common as $common) {
			$compiled[] = $this->compileCommon($common);
		}

		return $compiled;
	}

	/**
	 * Compile a common statement.
	 *
	 * @param  array $common
	 * @return array
	 */
	public function compileCommon($common)
	{
		$subCompiled = $this->compile($common['field'], $this->compileClause($common['value'], ['minimumShouldMatch']));

		return $this->compile('common', $subCompiled);
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
}
