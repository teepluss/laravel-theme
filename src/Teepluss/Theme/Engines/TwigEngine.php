<?php namespace Teepluss\Theme\Engines;

use Illuminate\Foundation\Application;
use Teepluss\Theme\Compilers\TwigCompiler;
use Illuminate\View\Engines\EngineInterface;

class TwigEngine implements EngineInterface {

    /**
     * Store the application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * Create a new view engine instance.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return \Teepluss\Theme\Engines\TwigEngine
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = array())
    {
        // Instance Twig compiler.
        $twigCompiler = new TwigCompiler($this->app['config'], $this->app['view']);

        return $twigCompiler->render($path, $data);
    }

}