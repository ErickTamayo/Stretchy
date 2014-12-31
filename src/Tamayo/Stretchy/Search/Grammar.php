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
				$this->compileBools($builder)
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
		$subCompile = $this->compile($match['field'], $this->compileClause($match['value']));

		return $this->compile('match', $subCompile);
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
		$compiled = array();

		//Compile must, must not and should sub clauses
		foreach (['must', 'mustNot', 'should'] as $subClause) {
			if (isset($bool['value']->$subClause)) {
				$compiled = array_merge($this->compile(Str::snake($subClause), $bool['value']->$subClause->toArray()), $compiled);
			}
		}

		$compiledClause = $this->compileClause($bool['value']);

		$compiled = isset($compiledClause) ? array_merge($compiled, $compiledClause) : $compiled;

		return $this->compile('bool', $compiled);
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
		$compiled = $clause->getAffectedConstraints();

		if (empty($compiled)) {
			return null;
		}

		return $compiled;
	}
}
