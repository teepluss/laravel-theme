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
        $configPath = __DIR__.'/../config/theme.php';

        // Publish config.
        $this->publishes([$configPath => config_path('theme.php')], 'config');
    }

    /**
     * Register service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/theme.php';

        // Merge config to allow user overwrite.
        $this->mergeConfigFrom($configPath, 'theme');

        // Temp to use in closure.
        $app = $this->app;

        // Add view extension.
        $this->app['view']->addExtension('twig.php', 'twig', function() use ($app)
        {
            return new Engines\TwigEngine($app);
        });

        // Register providers.
        $this->registerAsset();
        $this->registerTheme();
        //$this->registerWidget();
        $this->registerBreadcrumb();

        // Register commands.
        $this->registerThemeGenerator();
        $this->registerWidgetGenerator();
        $this->registerThemeDestroy();

        // Assign commands.
        $this->commands(
            'theme.create',
            'theme.widget',
            'theme.destroy'
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
            return new Asset();
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
            return new Theme($app['config'], $app['events'], $app['view'], $app['asset'], $app['files'], $app['breadcrumb']);
        });

        $this->app->alias('theme', 'Teepluss\Theme\Contracts\Theme');
    }

    /**
     * Register widget provider.
     *
     * @return void
     */
    // public function registerWidget()
    // {
    //     $this->app['widget'] = $this->app->share(function($app)
    //     {
    //         return new Widget($app['view']);
    //     });
    // }

    /**
     * Register breadcrumb provider.
     *
     * @return void
     */
    public function registerBreadcrumb()
    {
        $this->app['breadcrumb'] = $this->app->share(function($app)
        {
            return new Breadcrumb($app['files']);
        });
    }

    /**
     * Register generator of theme.
     *
     * @return void
     */
    public function registerThemeGenerator()
    {
        $this->app['theme.create'] = $this->app->share(function($app)
        {
            return new Commands\ThemeGeneratorCommand($app['config'], $app['files']);
        });
    }

    /**
     * Register generator of widget.
     *
     * @return void
     */
    public function registerWidgetGenerator()
    {
        $this->app['theme.widget'] = $this->app->share(function($app)
        {
            return new Commands\WidgetGeneratorCommand($app['config'], $app['files']);
        });
    }

    /**
     * Register theme destroy.
     *
     * @return void
     */
    public function registerThemeDestroy()
    {
        $this->app['theme.destroy'] = $this->app->share(function($app)
        {
            return new Commands\ThemeDestroyCommand($app['config'], $app['files']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('asset', 'theme', 'widget', 'breadcrumb');
    }

}