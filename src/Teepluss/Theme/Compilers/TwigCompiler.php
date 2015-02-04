<?php namespace Teepluss\Theme\Compilers;

use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_String;
use Twig_SimpleFunction;
use Twig_Function_Function;
use Twig_Loader_Filesystem;
use Illuminate\Config\Repository;
use Illuminate\View\Compilers\CompilerInterface;

class TwigCompiler implements CompilerInterface {

    /**
     * Twig compiler.
     *
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * View.
     *
     * @var \Illuminate\View\Factory
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
     * @param \Illuminate\Config\Repository $config
     * @param  \Illuminate\View\Factory $   view
     * @return \Teepluss\Theme\Compilers\TwigCompiler
     */
    public function __construct(Repository $config, $view)
    {
        $this->config = $config;

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
     * Twig compiler.
     *
     * @param  Twig_Loader_Filesystem $loader
     * @return Twig_Environment
     */
    public function getTwigCompiler($loader)
    {
        $this->twig = new Twig_Environment($loader, array(
           'cache'       => storage_path().'/views',
           'autoescape'  => false,
           'auto_reload' => true
        ));

        // Hook twig to do what you want.
        $hooks = $this->config->get('theme.engines.twig.hooks');
        $this->twig = $hooks($this->twig);

        // Get facades aliases.
        $aliases = $this->config->get('app.aliases');

        // Laravel alias to allow.
        $allows = $this->config->get('theme.engines.twig.allows');

        foreach ($aliases as $alias => $class)
        {
            // Nothing allow if not exists in twig config.
            if ( ! in_array($alias, $allows)) continue;

            // Clasname with namspacing.
            $className = '\\'.$alias;

            // Some method is not in facade like Str.
            if ( ! method_exists($className, 'getFacadeRoot'))
            {
                $this->twig->addGlobal($alias, new $className());
            }
            // Method support real facade.
            else
            {
                $this->twig->addGlobal($alias, $className::getFacadeRoot());
            }
        }

        /*$function = new Twig_SimpleFunction('call', function($function)
        {
            $args = func_get_args();
            $args = array_splice($args, 1);

            return call_user_func_array($function, $args);
        });

        $this->twig->addFunction($function);*/

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
        // View finder.
        $finder = $this->view->getFinder();

        // Get the list of view paths from the app.
        $paths = $finder->getPaths();

        // Get hints.
        $hints = $finder->getHints();

        // Get current theme uses.
        $currentThemeUses = \Theme::getThemeNamespace();

        if (isset($hints[$currentThemeUses]))
        {
            $paths = array_merge($paths, $hints[$currentThemeUses]);
        }

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
     * @return TwigCompiler
     */
    public function setData($data)
    {
        $this->data = $data;

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