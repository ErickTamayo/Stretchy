<?php

namespace Tamayo\Stretchy;

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
	public function __construct($hosts, $prefix, array $auth = null)
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
	 * @param  array
	 * @return array
	 */
	public function indexCreate($payload)
	{
		return $this->client->indices()->create($payload);
	}

	/**
	 * Delete an index in elastic search.
	 *
	 * @param  string $index
	 * @return array
	 */
	public function indexDelete($index)
	{
		return $this->client->indices()->delete($index);
	}

	/**
	 * Get the settings for one or multiple indices.
	 *
	 * @param  array $payload
	 * @return array
	 */
	public function indexGetSettings($payload)
	{
		return $this->client->indices()->getSettings($payload);
	}

	/**
	 * Perform an insertion into the engine.
	 *
	 * @param  array $payload
	 * @return array
	 */
	public function documentInsert($payload)
	{
		return $this->client->index($payload);
	}

	/**
	 * Perform an insertion into the engine.
	 *
	 * @param  array $payload
	 * @return array
	 */
	public function documentUpdate($payload)
	{
		return $this->client->update($payload);
	}

	/**
	 * Perform an insertion into elastic.
	 *
	 * @param  array $payload
	 * @return array
	 */
	public function documentDelete($payload)
	{
		return $this->client->delete($payload);
	}

	/**
	 * Get a document from elastic.
	 *
	 * @param  array $payload
	 * @return array
	 */
	public function documentGet($payload)
	{
		return $this->client->get($payload);
	}

	/**
	 * Perform a search in the engine.
	 *
	 * @param  array $payload
	 * @return array
	 */
	public function search($payload)
	{
		return $this->client->search($payload);
	}
}
