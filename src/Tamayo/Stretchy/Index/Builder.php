<?php namespace Tamayo\Stretchy\Index;

use Closure;
use Tamayo\Stretchy\Connection;
use Tamayo\Stretchy\Index\Grammar;

class Builder
{

    /**
     * Elastic Connection
     * 
     * @var \Tamayo\Stretchy\Connection
     */
    protected $connection;

    /**
     * Index Grammar
     * 
     * @var \Tamayo\Stretchy\Index\Grammar
     */
    protected $grammar;

    /**
     * Index Builder
     * 
     * @param \Tamayo\Stretchy\Connection $connection
     * @param Grammar                     $grammar
     */
    public function __construct(Connection $connection, Grammar $grammar)
    {
        $this->connection = $connection;
        $this->grammar = $grammar;
    }

    /**
     * Create a new index on Elastic
     * 
     * @param  string  $index
     * @param  Closure $callback
     * @return \Tamayo\Stretchy\Index\Blueprint
     */
    public function create($index, Closure $callback)
    {
        $blueprint = $this->createBlueprint($index);

        $blueprint->create();
        
        $callback($blueprint);

        $this->build($blueprint);
    }

    /**
     * Deletes an index on Elastic
     * 
     * @param  string $index
     * @return \Tamayo\Stretchy\Index\Blueprint
     */
    public function delete($index)
    {
        
    }

    /**
     * Create an alias on Elastic
     * 
     * @param  string $index
     * @param  string $alias
     * @return \Tamayo\Stretchy\Index\Blueprint
     */
    public function alias($index, $alias)
    {
        
    }

    /**
     * Delete an alias on Elastic
     * 
     * @param  string $alias
     * @return \Tamayo\Stretchy\Index\Blueprint
     */
    public function deleteAlias($alias)
    {
        
    }

    /**
     * Create a new blueprint for builder
     * 
     * @param  string $index
     * @param  Closure $callback
     * @return \Tamayo\Stretch\Index\Blueprint
     */
    protected function createBlueprint($index, Closure $callback = null)
    {
        return new Blueprint($index, $callback);
    }

    /**
     * Execute the blueprint to build / modify the table.
     *
     * @param  \Tamayo\Stretch\Index\Blueprint $blueprint
     * @return void
     */
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }
}