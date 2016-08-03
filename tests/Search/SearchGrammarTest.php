<?php

use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Search\Grammar;
use Tamayo\Stretchy\Search\Builder;
use Tamayo\Stretchy\Search\Processor;

class SearchGrammarTest extends PHPUnit_Framework_TestCase
{

    public function testBilderIndices()
    {
        $builder = $this->getBuilder();

        $this->assertEquals('{"index":"*"}', $builder->toJson());

        $builder->search('foo');

        $this->assertEquals('{"index":"foo"}', $builder->toJson());

        $builder->search(['foo', 'bar']);

        $this->assertEquals('{"index":"foo,bar"}', $builder->toJson());
    }

    public function testMatch()
    {
        $builder = $this->getBuilder();

        $builder->match('foo', 'bar');

        $this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":"bar"}}}}', $builder->toJson());
    }

    public function testMatchPhrase()
    {
        $builder = $this->getBuilder();

        $builder->matchPhrase('foo', 'bar');

        $this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":{"query":"bar","type":"phrase"}}}}}', $builder->toJson());
    }

    public function testMatchPhrasePrefix()
    {
        $builder = $this->getBuilder();

        $builder->matchPhrasePrefix('foo', 'bar');

        $this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":{"query":"bar","type":"phrase_prefix"}}}}}', $builder->toJson());
    }

    public function testSingleMultiMatch()
    {
        $builder = $this->getBuilder();

        $builder->multiMatch(['foo', 'bar'], 'baz');

        $this->assertEquals('{"index":"*","body":{"query":{"multi_match":{"query":"baz","fields":["foo","bar"]}}}}', $builder->toJson());

        $builder = $this->getBuilder();

        $builder->multiMatch(['foo', 'bar'], 'baz', ['tie_breaker' => 0.3]);

        $this->assertEquals('{"index":"*","body":{"query":{"multi_match":{"query":"baz","fields":["foo","bar"],"tie_breaker":0.3}}}}', $builder->toJson());
    }

    public function testBool()
    {
        $builder = $this->getBuilder();

        $builder->bool(function($query)
        {
            $query->must(function($must)
            {
                $must->match('foo', 'bar');
            });

            $query->mustNot(function($mustNot)
            {
                $mustNot->match('foo', 'baz');
            });

            $query->should(function($should)
            {
                $should->match('foo', 'bah');
                $should->match('foo', 'qux');
            });

            $query->minimumShouldMatch(1);
            $query->boost(3);
        });

        $json = json_decode( '{"index":"*","body":{"query":{"bool":{"should":[{"match":{"foo":"bah"}},{"match":{"foo":"qux"}}],"must":{"match":{"foo":"bar"}},"must_not":{"match":{"foo":"baz"}},"minimum_should_match":1,"boost":3}}}}', true);

        $this->assertEquals($json, $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);

        $builder = $this->getBuilder();

        $builder->bool(function($query)
        {
            $query->must('match', 'foo', 'bar')
                ->mustNot('match', 'foo', 'baz')
                ->should('match', 'foo', 'bah', ['zero_terms_query' => 'all'])
                ->minimumShouldMatch(2)
                ->boost(4);
        });

        $json = json_decode('{"index":"*","body":{"query":{"bool":{"should":{"match":{"foo":{"query":"bah","zero_terms_query":"all"}}},"must":{"match":{"foo":"bar"}},"must_not":{"match":{"foo":"baz"}},"minimum_should_match":2,"boost":4}}}}', true);

        $this->assertEquals($json, $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    public function testBoosting()
    {
        $builder = $this->getBuilder();

        $builder->boosting(function($query)
        {
            $query->positive(function($positive)
            {
                $positive->match('bar', 'baz');
            });

            $query->negative(function($negative)
            {
                $negative->match('bar', 'bah');
            });

            $query->negativeBoost(0.2);
        });

        $this->assertEquals('{"index":"*","body":{"query":{"boosting":{"positive":{"match":{"bar":"baz"}},"negative":{"match":{"bar":"bah"}},"negative_boost":0.2}}}}', $builder->toJson());
    }

    public function testCommonTerms()
    {
        $builder = $this->getBuilder();

        $builder->common('bar', 'The brown fox', ['cutoff_frequency' => 0.001, 'minimum_should_match' => ['low_freq' => 2, 'high_freq' => 3]]);

        $json = $builder->toJson();

        $this->assertEquals('{"index":"*","body":{"query":{"common":{"bar":{"query":"The brown fox","cutoff_frequency":0.001,"minimum_should_match":{"low_freq":2,"high_freq":3}}}}}}', $builder->toJson());
    }

    public function testConstantScore()
    {
        $builder = $this->getBuilder();

        $builder->constantScore(function($constantScore)
        {
            $constantScore->filter(function($filter)
            {
                $filter->term('foo', 'bar');
            });
        });

        $this->assertEquals('{"index":"*","body":{"query":{"constant_score":{"filter":{"term":{"foo":"bar"}}}}}}', $builder->toJson());
    }

    public function testDisMax()
    {
        $builder = $this->getBuilder();

        $builder->disMax(function($disMax) {
            $disMax->tieBreaker(0.7);
            $disMax->boost(1.2);

            $disMax->queries(function($queries) {
                $queries->term('age', 34);
                $queries->term('age', 35);
            });
        });

        $json = json_decode('{"index":"*","body":{"query":{"dis_max":{"boost":1.2,"tie_breaker":0.7,"queries":[{"term":{"age":34}},{"term":{"age":35}}]}}}}',true);

        $this->assertEquals($json, $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    public function testSingleFuzzy()
    {
        $builder = $this->getBuilder();

        $builder->fuzzy('price', 12, ['fuzziness' => 2]);

        $this->assertEquals('{"index":"*","body":{"query":{"fuzzy":{"price":{"value":12,"fuzziness":2}}}}}', $builder->toJson());
    }

    public function testSingleGeo()
    {
        $builder = $this->getBuilder();

        $builder->geoShape('location', 13, 53, 14, 52);

        $this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"match_all":{}},"filter":{"geo_shape":{"location":{"shape":{"type":"envelope","coordinates":[[13,53],[14,52]]},"relation":"within"}}}}}}}', $builder->toJson());
    }

    public function testPreindexedGeoShape()
    {
        $builder = $this->getBuilder();

        $builder->preindexedGeoShape('location', 'DEU', 'countries', 'shapes', 'location');

        $this->assertEquals('{"index":"*","body":{"query":{"bool":{"must":{"match_all":{}},"filter":{"geo_shape":{"location":{"indexed_shape":{"id":"DEU","type":"countries","index":"shapes","path":"location"}}}}}}}}', $builder->toJson());
    }

    public function testHasChild()
    {
        $builder = $this->getBuilder();

        $builder->hasChild('blog_tag', function ($query) {
            $query->scoreMode('sum');
            $query->minChildren(2);
            $query->maxChildren(10);
            $query->query('term', 'tag', 'something');
        });

        $json = json_decode('{"index":"*","body":{"query":{"has_child":{"type":"blog_tag","score_mode":"sum","min_children":2,"max_children":10,"query":{"term":{"tag":"something"}}}}}}', true);

        $this->assertEquals($json, $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);

        $builder = $this->getBuilder();

        $builder->hasChild('blog_tag', 'term', 'tag', 'something', ['boost' => 2.0]);

        $json = json_decode('{"index":"*","body":{"query":{"has_child":{"type":"blog_tag","query":{"term":{"tag":{"query":"something","boost":2}}}}}}}', true);

        $this->assertEquals($json , $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    public function testHasParent()
    {
        $builder = $this->getBuilder();

        $builder->hasParent('blog', function ($query) {
            $query->scoreMode('score');
            $query->query('term', 'tag', 'something');
        });

        $json = json_decode('{"index":"*","body":{"query":{"has_parent":{"parent_type":"blog","score_mode":"score","query":{"term":{"tag":"something"}}}}}}', true);

        $this->assertEquals($json, $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);

        $builder = $this->getBuilder();

        $builder->hasParent('actor', 'term', 'tag', 'something');

        $json = json_decode('{"index":"*","body":{"query":{"has_parent":{"parent_type":"actor","query":{"term":{"tag":"something"}}}}}}', true);

        $this->assertEquals($json, $builder->toArray(), '', $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    public function testIds()
    {
        $builder = $this->getBuilder();

        $builder->ids([2, 100], 'my_type');

        $json = $builder->toJson();

        $this->assertEquals('{"index":"*","body":{"query":{"ids":{"values":[2,100],"type":"my_type"}}}}', $json);
    }

    public function testIndices()
    {
        $builder = $this->getBuilder();

        $builder->indices(['index1', 'index2'], function($indices)
        {
            $indices->query(function($query)
            {
                $query->term('tag', 'wow');
            });

            $indices->noMatchQuery(function($noMatchQuery)
            {
                $noMatchQuery->term('tag', 'kow');
            });
        });

        $this->assertEquals('{"index":"*","body":{"query":{"indices":{"indices":["index1","index2"],"query":{"term":{"tag":"wow"}},"no_match_query":{"term":{"tag":"kow"}}}}}}', $builder->toJson());
    }

    public function testMatchAll()
    {
        $builder = $this->getBuilder();

        $builder->matchAll();

        $json = $builder->toJson();

        $this->assertEquals('{"index":"*","body":{"query":{"match_all":{}}}}', $json);

        $builder = $builder->newInstance();

        $builder->matchAll(['boost' => 1.2]);

        $json = $builder->toJson();

        $this->assertEquals('{"index":"*","body":{"query":{"match_all":{"boost":1.2}}}}', $json);
    }

    public function testMoreLikeThis()
    {
        $builder = $this->getBuilder();

        $builder->moreLikeThis(['title', 'description'], 'Once upon a time', ['min_term_freq' => 1, 'max_query_terms' => 12]);

        $this->assertEquals('{"index":"*","body":{"query":{"more_like_this":{"fields":["title","description"],"like":"Once upon a time","min_term_freq":1,"max_query_terms":12}}}}', $builder->toJson());

        $builder = $builder->newInstance();

        $builder->moreLikeThis(['name.first', 'name.last'], function($query) {
            $query->like('imdb', 'movies', '1');
            $query->like('imdb', 'movies', '2');
            $query->like('and potentially some more text here as well');
            $query->minTermFreq(1);
            $query->maxQueryTerms(12);
        });

        $this->assertEquals('{"index":"*","body":{"query":{"more_like_this":{"fields":["name.first","name.last"],"like":[{"_index":"imdb","_type":"movies","_id":"1"},{"_index":"imdb","_type":"movies","_id":"2"},"and potentially some more text here as well"],"min_term_freq":1,"max_query_terms":12}}}}', $builder->toJson());

        $builder = $builder->newInstance();

        $builder->moreLikeThis(['name.first', 'name.last'], function($query) {
            $query->like('marvel', 'quotes', [
                'name' => [
                    'first' => 'Ben',
                    'last' => 'Grim'
                ],
                'tweet' => 'You got no idea what I\'d... what I\'d give to be invisible.'
            ]);

            $query->like('marvel', 'quotes', '2');

            $query->minTermFreq(1);
            $query->maxQueryTerms(12);
        });

        $this->assertEquals('{"index":"*","body":{"query":{"more_like_this":{"fields":["name.first","name.last"],"like":[{"_index":"marvel","_type":"quotes","doc":{"name":{"first":"Ben","last":"Grim"},"tweet":"You got no idea what I\'d... what I\'d give to be invisible."}},{"_index":"marvel","_type":"quotes","_id":"2"}],"min_term_freq":1,"max_query_terms":12}}}}', $builder->toJson());
    }

    public function testNested()
    {
        $builder = $this->getBuilder();

        $builder->nested('obj1', 'avg', function ($query) {
            $query->bool(function($query) {
                $query->must('match', 'obj1.name', 'blue');
            });
        });

        $this->assertEquals('{"index":"*","body":{"query":{"nested":{"path":"obj1","score_mode":"avg","bool":{"must":{"match":{"obj1.name":"blue"}}}}}}}', $builder->toJson());
    }

    public function testPrefix()
    {
        $builder = $this->getBuilder();

        $builder->prefix('user', 'ki', ['boost' => 2]);

        $this->assertEquals('{"index":"*","body":{"query":{"prefix":{"user":{"prefix":"ki","boost":2}}}}}', $builder->toJson());
    }

    public function testQueryString()
    {
        $builder = $this->getBuilder();

        $builder->queryString(['last_name'], 'this AND that OR thus');

        $this->assertEquals('{"index":"*","body":{"query":{"query_string":{"fields":["last_name"],"query":"this AND that OR thus"}}}}', $builder->toJson());
    }

    public function testRange()
    {
        $builder = $this->getBuilder();

        $builder->range('created', ['gte' => 'now - 1d / d']);

        $this->assertEquals('{"index":"*","body":{"query":{"range":{"created":{"gte":"now - 1d \/ d"}}}}}', $builder->toJson());
    }

    public function testSingleTerm()
    {
        $builder = $this->getBuilder();

        $builder->term('foo', 'bar', ['boost' => 2]);

        $this->assertEquals('{"index":"*","body":{"query":{"term":{"foo":{"query":"bar","boost":2}}}}}', $builder->toJson());
    }

    public function testTerms()
    {
        $builder = $this->getBuilder();

        $builder->terms('tags', ['blue', 'pill']);

        $this->assertEquals('{"index":"*","body":{"query":{"terms":{"tags":["blue","pill"]}}}}', $builder->toJson());
    }

    public function testRaw()
    {
        $builder = $this->getBuilder();

        $builder->raw('{"query":{"match":{"foo":"bar"}}}');

        $this->assertEquals('{"index":"*","body":{"query":{"match":{"foo":"bar"}}}}', $builder->toJson());
    }

    public function getGrammar()
    {
        return new Grammar;
    }

    public function getConnection()
    {
        $connection = Mockery::mock('Tamayo\Stretchy\Connection');

        $connection->shouldReceive('getIndexPrefix')->andReturn('');

        return $connection;
    }

    public function getProcessor()
    {
        return new Processor;
    }

    public function getBuilder()
    {
        return new Builder($this->getConnection(), $this->getGrammar(), $this->getProcessor());
    }
}
