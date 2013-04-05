<?php namespace Teepluss\Theme;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('teepluss/theme');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerAsset();

		$this->registerTheme();
	}

	public function registerTheme()
	{
		$this->app['theme'] = $this->app->share(function($app)
		{
			return new Theme($app['config'], $app['view'], $app['asset']);
		});
	}

	public function registerAsset()
	{
		$this->app['asset'] = $this->app->share(function($app)
		{
			return new Asset;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('theme');
	}

}