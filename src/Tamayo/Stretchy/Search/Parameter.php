<?php namespace Tamayo\Stretchy\Search;

class Parameter
{
    /**
     * List of the all available clauses.
     *
     * @var array
     */
    static $parameters = [
        'match', 'multi_match', 'bool', 'should', 'must', 'must_not', 'filter', 'minimum_should_match',
        'boost', 'boosting', 'positive', 'negative', 'negative_boost', 'term', 'common', 'constant_score',
        'dis_max', 'tie_breaker', 'queries', 'fuzzy', 'match_all', 'geo_shape', 'has_child', 'score_mode',
        'min_children', 'max_children', 'has_parent', 'query', 'ids', 'indices', 'no_match_query', 'more_like_this',
        'like', 'unlike', 'fields', 'min_term_freq', 'max_query_terms', 'min_doc_freq', 'max_doc_freq', 'min_word_length',
        'max_word_length', 'stop_words', 'analyzer', 'include', 'boost_terms', 'nested', 'prefix', 'query_string',
        'default_field', 'default_operator', 'analyzer', 'allow_leading_wildcard', 'lowercase_expanded_terms',
        'enable_position_increments', 'fuzzy_max_expansions', 'fuzziness', 'fuzzy_prefix_length', 'phrase_slop',
        'analyze_wildcard', 'auto_generate_phrase_queries', 'max_determinized_states', 'lenient' , 'locale', 'time_zone',
        'regexp', 'range', 'filter', 'terms', 'index', 'type', 'id', 'path', 'routing',
     ];

    /**
     * Checks if a parameter is valid.
     *
     * @param  string  $clause
     * @return boolean
     */
    public static function isValid($clause)
    {
        return in_array($clause, self::$parameters);
    }
}