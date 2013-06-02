<?php namespace Teepluss\Theme;

use Illuminate\Support\ClassLoader;
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
        // Autoload for widget factory.
    	ClassLoader::addDirectories(array(
    		app_path().'/widgets'
    	));

        $this->package('teepluss/theme');
    }

	/**
	 * Register service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerAsset();
		$this->registerTheme();
        $this->registerWidget();

        $this->registerThemeGenerator();

        $this->commands(
            'generate.theme'
        );
	}

    /**
     * Register asset provider.
     *
     * @return void
     */
    public function registerAsset()
    {
        $this->app['asset'] = $this->app->share(function($app)
        {
            return new Asset;
        });
    }

    /**
     * Register theme provider.
     *
     * @return void
     */
	public function registerTheme()
	{
		$this->app['theme'] = $this->app->share(function($app)
		{
			return new Theme($app['config'], $app['view'], $app['asset']);
		});
	}

    /**
     * Register widget provider.
     *
     * @return void
     */
    public function registerWidget()
    {
         $this->app['widget'] = $this->app->share(function($app)
        {
            return new Widget($app['view']);
        });
    }

    /**
     * Reguster generator of theme.
     *
     * @return void
     */
    public function registerThemeGenerator()
    {
        $this->app['generate.theme'] = $this->app->share(function($app)
        {
            return new Commands\ThemeGeneratorCommand($app['config'], $app['files']);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('theme', 'asset', 'widget');
	}

}