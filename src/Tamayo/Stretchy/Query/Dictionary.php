<?php namespace Tamayo\Stretchy\Query;

class Dictionary
{
	/**
	 * Bool query constraints.
	 *
	 * @var array
	 */
	public static $bool = [
		'constraints' => ['minimum_should_match', 'boost'],
		'subqueries'  => ['must', 'must_not', 'should'],
	];

	/**
	 * Boosting query constraints.
	 *
	 * @var array
	 */
	public static $boosting = [
		'constraints' => ['negative_boost'],
		'subqueries'  => ['positive', 'negative'],
	];

	/**
	 * Common query constraints.
	 *
	 * @var array
	 */
	public static $common = [
		'constraints' => ['query', 'cutoff_frequency', 'low_freq_operator', 'high_freq_operator', 'boost', 'analyzer', 'disable_coord'],
		'subclauses'  => ['minimum_should_match' => ['low_freq', 'high_freq']]
	];

	/**
	 * Geo shape query constraints.
	 *
	 * @var array
	 */
	public static $constantScore = [
		'constraints' => ['boost'],
		'subqueries'  => ['filter', 'query']
	];

	/**
	 * Dis max query constraints.
	 *
	 * @var array
	 */
	public static $disMax = [
		'constraints' => ['tie_breaker', 'boost'],
		'subqueries'  => ['queries']
	];

	/**
	 * Filtered query constraints.
	 *
	 * @var array
	 */
	public static $filtered = [
		'constraints' => ['tie_breaker', 'boost'],
		'subqueries'  => ['filter', 'query'],
	];

	/**
	 * Geo shape query constraints.
	 *
	 * @var array
	 */
	public static $geoShape = [
		'subclauses'  => ['shape' => ['type', 'coordinates'], 'indexed_shape' => ['id', 'type', 'index', 'location', 'path']]
	];

	/**
	 * Has child query constraints.
	 *
	 * @var array
	 */
	public static $hasChild = [
		'constraints' => ['type', 'score_mode', 'min_children', 'max_children'],
		'subqueries'  => ['query']
	];

	/**
	 * Has parent query constraints.
	 *
	 * @var array
	 */
	public static $hasParent = [
		'constraints' => ['type', 'score_mode', 'parent_type'],
		'subqueries'  => ['query']
	];

	/**
	 * Ids query constraints.
	 *
	 * @var array
	 */
	public static $ids = [
		'constraints' => ['type', 'values']
	];

	/**
	 * Indices query constraints.
	 *
	 * @var array
	 */
	public static $indices = [
		'subqueries'  => ['query', 'no_match_query']
	];

	/**
	 * Match all query constraints.
	 *
	 * @var array
	 */
	public static $matchAll = [
		'constraints' => ['boost']
	];

	/**
	 * More like this query constraints.
	 *
	 * @var array
	 */
	public static $moreLikeThis = [
		'constraints' => ['fields', 'like_text', 'min_term_freq', 'max_query_terms', 'docs', 'ids', 'include',
		'exclude', 'percent_terms_to_match', 'stop_words', 'min_doc_freq', 'max_doc_freq', 'min_word_length',
		'max_word_length', 'boost_terms', 'boost', 'analyzer']
	];

	/**
	 * Nested query constraints.
	 *
	 * @var array
	 */
	public static $nested = [
		'constraints' => ['path', 'score_mode'],
		'subqueries'  => ['query', 'filter']
	];

	/**
	 * Prefix query constraints.
	 *
	 * @var array
	 */
	public static $prefix = [
		'constraints' => ['value', 'boost']
	];

	/**
	 * Fuzzy query constraints.
	 *
	 * @var array
	 */
	public static $fuzzy = [
		'constraints' => ['value', 'boost', 'fuzziness', 'prefix_length', 'max_expansions']
	];

	/**
	 * Fuzzy like this field query constraints.
	 *
	 * @var array
	 */
	public static $fuzzyLikeThisField = [
		'constraints' => ['like_text', 'ignore_tf', 'max_query_terms', 'fuzziness', 'prefix_length', 'boost', 'analyzer']
	];

	/**
	 * Fuzzy like this query constraints.
	 *
	 * @var array
	 */
	public static $fuzzyLikeThis = [
		'constraints' => ['fields', 'like_text', 'ignore_tf', 'max_query_terms', 'fuzziness', 'prefix_length', 'boost', 'analyzer']
	];

	/**
	 * Multi match query constraints.
	 *
	 * @var array
	 */
	public static $multiMatch = [
		'constraints' => ['query', 'fields', 'type', 'tie_breaker', 'analyzer', 'boost', 'operator', 'minimum_should_match', 'fuzziness', 'prefix_length', 'max_expansions', 'rewrite', 'zero_terms_query', 'cutoff_frequency']
	];

	/**
	 * Match query constraints.
	 *
	 * @var array
	 */
	public static $match = [
		'constraints' => ['query', 'fields', 'type', 'tie_breaker', 'analyzer', 'boost', 'operator', 'minimum_should_match', 'fuzziness', 'prefix_length', 'max_expansions', 'rewrite', 'zero_terms_query', 'cutoff_frequency', 'lenient']
	];

	/**
	 * Range query constraints.
	 *
	 * @var array
	 */
	public static $range = [
		'constraints' => ['gte', 'gt', 'lte', 'lt', 'boost', 'time_zone']
	];

	/**
	 * Term query constraints.
	 *
	 * @var array
	 */
	public static $term = [
		'constraints' => ['boost', 'value']
	];

	/**
	 * Query string query constraints.
	 *
	 * @var array
	 */
	public static $queryString = [
		'constraints' => ['query', 'default_field', 'default_operator', 'analyzer', 'allow_leading_wildcard', 'lowercase_expanded_terms', 'enable_position_increments', 'fuzzy_max_expansions', 'fuzziness', 'fuzzy_prefix_length', 'phrase_slop', 'boost', 'analyze_wildcard', 'auto_generate_phrase_queries', 'minimum_should_match', 'lenient', 'locale']
	];

	/**
	 * Simple query string query constraints.
	 *
	 * @var array
	 */
	public static $simpleQueryString = [
		'constraints' => ['query', 'fields', 'default_operator', 'analyzer', 'flags', 'lowercase_expanded_terms', 'locale', 'lenient']
	];

	/**
	 * Regex query constraints.
	 *
	 * @var array
	 */
	public static $regex = [
		'constraints' => ['value', 'boost', 'flags']
	];

	/**
	 * Terms query constraints.
	 *
	 * @var array
	 */
	public static $terms = [
		'constraints' => ['minimum_should_match']
	];

	/**
	 * Wildcard query constraints.
	 *
	 * @var array
	 */
	public static $wildcard = [
		'constraints' => ['value', 'boost']
	];

	/**
	 *  query constraints.
	 *
	 * @var array
	 */
	// public static $ = [
	// 	'constraints' => [],
	// 	'subqueries'  => [],
	// 	'subclauses'  => []
	// ];

}
