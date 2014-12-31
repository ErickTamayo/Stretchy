<?php namespace Tamayo\Stretchy\Facades;

use Illuminate\Support\Facades\Facade;

class Stretchy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'stretchy.search'; }
}
