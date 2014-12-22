<?php namespace Tamayo\Stretchy\Index;

use Tamayo\Stretchy\Connection;

class Grammar
{
    /**
     * Compile the create Index command
     * 
     * @return array
     */
    public function compileIndexCreate(Blueprint $blueprint, Connection $connection)
    {
        $compiled = array();

        $compiled['index'] = $this->getIndexName($blueprint, $connection);

        $compiled['body']  = $this->compileSettings($blueprint);

        return $compiled;
    }


    /**
     * Get the index name
     * 
     * @param  Blueprint  $blueprint
     * @param  Connection $connection
     * @return string
     */
    public function getIndexName(Blueprint $blueprint, Connection $connection)
    {
        return $connection->getIndexPrefix().$blueprint->getIndex();
    }

    /**
     * Compile the settings for the index
     * 
     * @param  Blueprint $blueprint
     * @return array
     */
    public function compileSettings(Blueprint $blueprint)
    {
        $compiled = array();

        $compiled['settings'] = array_merge($this->compileShards($blueprint), $this->compileReplicas($blueprint));

        return $compiled;
    }

    /**
     * Compile the shard counts
     * @param  Blueprint $blueprint
     * @return array
     */
    public function compileShards(Blueprint $blueprint)
    {
        $compiled = array();

        $compiled['number_of_shards'] = $blueprint->getShards();

        return $compiled;
    }

    /**
     * Compile the replica count
     * 
     * @param  Blueprint $blueprint
     * @return array
     */
    public function compileReplicas(Blueprint $blueprint)
    {
        $compiled = array();

        $compiled['number_of_replicas'] = $blueprint->getReplicas();

        return $compiled;
    }

}