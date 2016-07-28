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
		//Lumen users need to copy the config file over to /config themselves
        //and it needs to be pulled in with $this->app->configure().
        if (str_contains($this->app->version(), 'Lumen')) {
            $this->app->configure('stretchy');
        }
        //Laravel users can run artisan config:publish and config will be
        //automatically read in with directory scanning.
        else {
			$this->publishes([
					__DIR__.'/../config/stretchy.php' => config_path('stretchy.php')
				], 'config');
        }
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
