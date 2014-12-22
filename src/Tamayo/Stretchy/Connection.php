<?php namespace Tamayo\Stretchy;

use Config;
use Elasticsearch\Client;

class Connection
{

    /**
     * ElasticSearch Client
     * 
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * Set up a new elastic client
     */
    public function __construct()
    {
        $this->client = new Client(['hosts' => (array) Config::get('stretchy::host')]);
    }

    /**
     * Get the index prefix from the configuration file
     * 
     * @return string
     */
    public function getIndexPrefix()
    {
        return Config::get('stretchy::prefix', '');
    }
}