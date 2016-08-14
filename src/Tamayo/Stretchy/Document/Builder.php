<?php

namespace Tamayo\Stretchy\Document;

use Closure;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Document\Grammar;
use Tamayo\Stretchy\Document\Processor;
use Tamayo\Stretchy\Builder as BaseBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Tamayo\Stretchy\Exceptions\TypeMustBeDefinedException;
use Tamayo\Stretchy\Exceptions\IndexMustBeDefinedException;

class Builder extends BaseBuilder {

	/**
	 * Index Processor.
	 *
	 * @var \Tamayo\Stretchy\Index\Processor
	 */
	protected $processor;

	/**
	 * Index Builder.
	 *
	 * @param \Tamayo\Stretchy\Connection $connection
	 * @param Grammar                     $grammar
	 */
	public function __construct(Connection $connection, Grammar $grammar, Processor $processor)
	{
		parent::__construct($connection, $grammar);

		$this->processor = $processor;
	}

	/**
	 * Insert a document into elasticsearch.
	 *
	 * @param  array  $payload
	 * @return mixed
	 */
	public function insert(array $payload)
	{
		$this->validate();

		$compiled = $this->grammar->compileInsert($this, $payload);

		return $this->processor->processInsert($this, $this->connection->documentInsert($compiled));
	}

	/**
	 * Update a document from elasticsearch.
	 *
	 * @param  array  $payload
	 * @return mixed
	 */
	public function update(array $payload)
	{
		$this->validate();

		$compiled = $this->grammar->compileUpdate($this, $payload);

		return $this->processor->processUpdate($this, $this->connection->documentUpdate($compiled));
	}

	/**
	 * Get a document from elasticsearch.
	 *
	 * @param  array  $payload
	 * @return mixed
	 */
	public function get()
	{
		$this->validate();

		$compiled = $this->grammar->compileGet($this);

		try {
			return $this->processor->processGet($this, $this->connection->documentGet($compiled));
		} catch (Missing404Exception $e) {
			return null;
		}
	}

	/**
	 * Delete a document from elasticsearch.
	 *
	 * @param  array  $payload
	 * @return mixed
	 */
	public function delete()
	{
		$this->validate();

		$compiled = $this->grammar->compileDelete($this);

		return $this->processor->processDelete($this, $this->connection->documentDelete($compiled));
	}

	/**
	 * Check if the builder is valid.
	 *
	 * @return void
	 * @throws \Tamayo\Stretchy\Exceptions\TypeMustBeDefinedException
	 * @throws \Tamayo\Stretchy\Exceptions\IndexMustBeDefinedException
	 */
	private function validate()
	{
		if(! $this->indexIsDefined()) {
			throw new IndexMustBeDefinedException("To delete a document, index must be definded", 1);
		}

		if(! $this->typeIsDefined()) {
			throw new TypeMustBeDefinedException("To delete a document, type must be defined", 1);
		}
	}
}
