<?php namespace Tamayo\Stretchy\Search\Clauses;

class MoreLikeThis extends Clause
{
	/**
	 * Available constraints for set in the clause.
	 *
	 * @var array
	 */
	protected $constraints = ['fields', 'like_text', 'min_term_freq', 'max_query_terms', 'docs', 'ids'];
}
