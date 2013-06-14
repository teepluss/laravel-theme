<?php namespace Teepluss\Theme\Compilers;

use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\Environment;;

use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_String;
use Twig_SimpleFunction;
use Twig_Function_Function;
use Twig_Loader_Filesystem;

class TwigCompiler implements CompilerInterface {

    /**
     * Twig compiler.
     *
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * View.
     *
     * @var \Illuminate\View\Environment
     */
    protected $view;

    /**
     * Data passing to twig.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Create a compiler instance.
     *
     * @param  \Illuminate\View\Environment  $view
     * @param  Asset  $asset
     * @return void
     */
    public function __construct(Environment $view)
    {
        $this->view = $view;
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        return false;
    }

    /**
     * Determine if the given view is expired.
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path)
    {
        return false;
    }

    /**
     * [getTwigCompiler description]
     * @param  [type] $loader [description]
     * @return [type]         [description]
     */
    public function getTwigCompiler($loader)
    {
        $this->twig = new Twig_Environment($loader, array(
           'cache'       => storage_path().'/views',
           'autoescape'  => false,
           'auto_reload' => true,
        ));

        $aliases = \Config::get('app.aliases');

        // Laravel alias to allow.
        $allows = \Config::get('theme::twig.allows');

        foreach ($aliases as $alias => $class)
        {
            if ( ! in_array($alias, $allows)) continue;

            $className = '\\'.$alias;

            if ( ! method_exists($className, 'getFacadeRoot'))
            {
                $this->twig->addGlobal($alias, new $className());
            }
            else
            {
                $this->twig->addGlobal($alias, $className::getFacadeRoot());
            }
        }

        $function = new Twig_SimpleFunction('call', function($function)
        {
            $args = func_get_args();
            $args = array_splice($args, 1);

            return call_user_func_array($function, $args);
        });

        $this->twig->addFunction($function);

        return $this->twig;
    }

    /**
     * Get compiler.
     *
     * @return Twig
     */
    public function getCompiler()
    {
        return $this->twig;
    }

    /**
     * Compile the view at the given path.
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path)
    {
        // Get the list of view paths from the app.
        $paths = $this->view->getFinder()->getPaths();

        // Sort the paths, longest first.
        usort($paths, function ($a, $b)
        {
            return strlen($b) - strlen($a);
        });

         // Get the directory the requested view sits in.
        $viewdir = dirname($path);

        // Match it to a view path registered in config::view.paths
        foreach ($paths as $dir)
        {
            if (stristr($viewdir, $dir))
            {
                $path = str_replace($dir.'/', '', $path);
                break;
            }
        }

        // Create a loader for this template.
        $loader = new Twig_Loader_Filesystem($paths);

        // Instance compiler.
        $twig = $this->getTwigCompiler($loader);

        return $twig->render($path, $this->data);
    }

    /**
     * Compile from string.
     *
     * @param  string $str
     * @return string
     */
    public function compileString($str)
    {
        $loader = new Twig_Loader_String();

        // Instance compiler.
        $twig = $this->getTwigCompiler($loader);

        return $twig->render($str, $this->data);
    }

    /**
     * Pass data to compile template.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        // Merge with shared data.
        $this->data = array_merge($this->view->getShared(), $data);

        return $this;
    }

    /**
     * Compile with data.
     *
     * @param  string $path
     * @param  mixed  $data
     * @return string
     */
    public function render($path, $data)
    {
        return $this->setData($data)->compile($path);
    }

}