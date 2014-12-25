<?php namespace Tamayo\Stretchy;

use Elasticsearch\Client;

class Connection
{

	/**
	 * ElasticSearch Client.
	 *
	 * @var \Elasticsearch\Client
	 */
	protected $client;

	/**
	 * Available Authentication Options.
	 *
	 * @var array
	 */
	protected $authOptions = ['Basic', 'Digests', 'NTLM', 'Any'];

	/**
	 * Index Prefix.
	 *
	 * @var string
	 */
	protected $indexPrefix = '';

	/**
	 * Set up a new elastic client.
	 *
	 * @return void
	 */
	public function __construct($container, $hosts, $prefix, array $auth = null)
	{
		$this->client = new Client(['hosts' => (array) $hosts]);

		$this->indexPrefix = $prefix;
	}

	/**
	 * Get the index prefix.
	 *
	 * @return string
	 */
	public function getIndexPrefix()
	{
		return $this->indexPrefix;
	}

	/**
	 * Create a new index in elasticsearch.
	 *
	 * @return string
	 */
	public function indexCreate($payload)
	{
		return $this->client->indices()->create($payload);
	}
}
