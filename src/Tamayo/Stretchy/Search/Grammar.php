<?php namespace Tamayo\Stretchy\Search;

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
				$this->compileMatches($builder)
			);
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

		return $this->compile('query', $this->$method($statement['statement']));
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

		foreach ($builder->matches as $match) {
			$this->compile('query', $this->compileMatch($match), $compiled);
		}

		return $compiled;
	}

	/**
	 * Compile a match statement.
	 *
	 * @param  \Tamayo\Stretchy\Search\Builder $builder
	 * @return array
	 */
	protected function compileMatch($match)
	{
		$subCompile = $this->compile(array_keys($match)[0], $this->compileClause(array_values($match)[0]));

		return $this->compile('match', $subCompile, $compiled);
	}

	/**
	 * Compile the a clause with its contraints.
	 *
	 * @param  \Tamayo\Stretchy\Search\Clauses\Clause $clause
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
