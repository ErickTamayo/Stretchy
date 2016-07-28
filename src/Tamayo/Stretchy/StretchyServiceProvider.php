<?php namespace Tamayo\Stretchy;

use Tamayo\Stretchy\Connection;
use Illuminate\Support\ServiceProvider;

class StretchyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('stretchy.document', 'Tamayo\Stretchy\Document\Builder');
		$this->app->bind('stretchy.index', 'Tamayo\Stretchy\Index\Builder');
		$this->app->bind('stretchy.search', 'Tamayo\Stretchy\Query\Builder');

		$this->app->singleton('Tamayo\Stretchy\Connection', function($app)
			{
				return new Connection(
					$app['config']->get('stretchy::hosts'),
					$app['config']->get('stretchy::prefix'),
					$app['config']->get('stretchy::auth')
				);
			});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
