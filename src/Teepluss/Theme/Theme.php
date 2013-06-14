<?php namespace Teepluss\Theme;

use Closure;
use ReflectionClass;
use Illuminate\Http\Response;
use Illuminate\View\Environment;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Symfony\Component\HttpFoundation\Cookie;

use Teepluss\Theme\Compilers\TwigCompiler;

class Theme {

	/**
	 * Repository config.
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Theme configuration.
	 *
	 * @var mixed
	 */
	protected $themeConfig;

	/**
	 * Environment view.
	 *
	 * @var Illuminate\View\Environment
	 */
	protected $view;

	/**
	 * Asset.
	 *
	 * @var Teepluss\Assets
	 */
	protected $asset;

	/**
	 * Filesystem.
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * The name of theme.
	 *
	 * @var string
	 */
	protected $theme;

	/**
	 * The name of layout.
	 *
	 * @var string
	 */
	protected $layout;

	/**
	 * Regions in the theme.
	 *
	 * @var array
	 */
	protected $regions = array();

	/**
	 * Cookie var.
	 *
	 * @var Cookie
	 */
	protected $cookie;

	/**
	 * Create a new theme instance.
	 *
	 * @param  \Illuminate\Config\Repository     $config
	 * @param  \Illuminate\View\Environment      $view
	 * @param  \Teepluss\Theme\asset             $asset
	 * @param  \Illuminate\Filesystem\Filesystem $files
	 * @return void
	 */
	public function __construct(Repository $config, Environment $view, Asset $asset, Filesystem $files)
	{
		$this->config = $config;

		$this->view = $view;

		$this->asset = $asset;

		$this->files = $files;

		// Default theme.
		$this->theme  = $this->getConfig('themeDefault');

		// Default layout.
		$this->layout = $this->getConfig('layoutDefault');
	}

	/**
	 * Get theme config.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getConfig($key = null)
	{
		if ( ! $this->themeConfig)
		{
			$this->themeConfig = $this->config->get('theme::config');
		}

		if ($this->theme and ! isset($this->themeConfig['themes'][$this->theme]))
		{
			$this->themeConfig['themes'][$this->theme] = null;

			try
			{
				$minorConfigPath = $this->themeConfig['themeDir'].'/'.$this->theme.'/config.php';

				$this->themeConfig['themes'][$this->theme] = $this->files->getRequire($minorConfigPath);
			}
			catch (\Illuminate\Filesystem\FileNotFoundException $e)
			{
				// File not found.
			}
		}

		$this->themeConfig = $this->evaluateConfig($this->themeConfig);

		if ( ! is_null($key))
		{
			return array_get($this->themeConfig, $key);
		}

		return $this->themeConfig;
	}

	/**
	 * Evaluate major config with minor.
	 *
	 * Config minor is at public folder [theme]/config.php,
	 * the high piority is on package config, that will
	 * override minor.
	 *
	 * @param  mixed $config
	 * @return mixed
	 */
	protected function evaluateConfig($config)
	{
		if ( ! isset($config['themes'][$this->theme]))
		{
			return $config;
		}

		$minorConfig = $config['themes'][$this->theme];

		if (array_key_exists('events', $minorConfig))
		{
			foreach ($minorConfig['events'] as $event => $action)
			{
				if ($event == 'beforeRenderThemeWithLayout')
				{
					$minorConfig['events'][$event] = array(
						$this->theme.ucfirst($this->layout) => $action
					);
				}

				$minorConfig['events'][$event] = array($this->theme => $action);
			}
		}

		$config = array_replace_recursive($minorConfig, $config);
		unset($config['themes']);

		return $config;
	}

	/**
	 * Fire event to config listener.
	 *
	 * @param  string $event
	 * @param  mixed  $args
	 * @return void
	 */
	public function fire($event, $args)
	{
		$onEvent = $this->getConfig('events.'.$event);

		if ($onEvent instanceof Closure)
		{
			$onEvent($args);
		}
	}

	/**
	 * Set up a theme name.
	 *
	 * @param  string $theme
	 * @return Theme
	 */
	public function theme($theme = null)
	{
		// If theme name is not set, so use default from config.
		if ($theme != false)
		{
			$this->theme = $theme;
		}

		// Fire event before set up theme.
		$this->fire('before', $this);

		// Add asset path to asset container.
		$this->asset->addPath($this->path().'/'.$this->getConfig('containerDir.asset'));

		// Fire event on set theme.
		$this->fire('onSetTheme.'.$this->theme, $this);

		return $this;
	}

	/**
	 * Alias of theme method.
	 *
	 * @param  string $theme
	 * @return Theme
	 */
	public function uses($theme = null)
	{
		return $this->theme($theme);
	}

	/**
	 * Set up a layout name.
	 *
	 * @param  string $layout
	 * @return Theme
	 */
	public function layout($layout)
	{
		// If layout name is not set, so use default from config.
		if ($layout != false)
		{
			$this->layout = $layout;
		}

		// Fire event after set layout.
		$this->fire('onSetLayout.'.$this->layout, $this);

		return $this;
	}

	/**
	 * Get a theme path.
	 *
	 * @return string
	 */
	public function path()
	{
		$themeDir = $this->getConfig('themeDir');

		return $themeDir.'/'.$this->theme;
	}

	/**
	 * Set a place to regions.
	 *
	 * @param  string $region
	 * @param  string $value
	 * @return Theme
	 */
	public function set($region, $value)
	{
		$this->regions[$region] = $value;

		return $this;
	}

	/**
	 * Append a place to existing region.
	 *
	 * @param  string $region
	 * @param  string $value
	 * @return Theme
	 */
	public function append($region, $value)
	{
		// If region not found, create a new region.
		if (isset($this->regions[$region]))
		{
			$this->regions[$region] .= $value;
		}
		else
		{
			$this->set($region, $value);
		}

		return $this;
	}

	/**
	 * Set up a partial.
	 *
	 * @param  string $view
	 * @param  array  $args
	 * @return mixed
	 */
	public function partial($view, $args = array())
	{
		$partialDir = $this->getConfig('containerDir.partial');

		$partial = '';

		if ( ! $this->view->exists($partialDir.'.'.$view))
		{
			throw new UnknownPartialFileException("Partial view [$view] not found.");
		}

		$partial = $this->view->make($partialDir.'.'.$view, $args)->render();

		$this->regions[$view] = $partial;

		return $this->regions[$view];
	}

	/**
	 * Widget instance.
	 *
	 * @param  string $className
	 * @param  array  $attributes
	 * @return Teepluss\Theme\Widget
	 */
	public function widget($className, $attributes = array())
	{
		static $widgets = array();

		if ( ! $instance = array_get($widgets, $className))
		{
			// Add theme location to view paths.
			$this->view->addLocation(public_path().'/'.$this->path());

			$reflector = new ReflectionClass($className);

			if ( ! $reflector->isInstantiable())
			{
				throw new UnknownWidgetClassException("Widget target [$className] is not instantiable.");
			}

			$instance = $reflector->newInstance($this->config, $this->view, $this->asset);

			array_set($widgets, $className, $instance);
		}

		$instance->setAttributes($attributes);

		$instance->beginWidget();

		$instance->endWidget();

		return $instance;
	}

	/**
	 * Hook a partial before rendering.
	 *
	 * @param  mixed   $view
	 * @param  closure $callback
	 * @return void
	 */
	public function partialComposer($view, $callback)
	{
		$partialDir = $this->getConfig('containerDir.partial');

		if ( ! is_array($view))
		{
			$view = array($view);
		}

		$view = array_map(function($v) use ($partialDir)
		{
			return $partialDir.'.'.$v;
		},
		$view);

		$this->view->composer($view, $callback);
	}

	/**
	 * Parses and compiles strings by using blade template system.
	 *
	 * @param  string  $str
	 * @param  array   $data
	 * @param  boolean $phpCompile
	 * @return string
	 */
	public function blader($str, $data = array(), $phpCompile = true)
	{
		if ($phpCompile == false)
		{
			$patterns = array('|<\?|', '|<\?php|', '|<\%|', '|\?>|', '|\%>|');
			$replacements = array('&lt;?', '&lt;php', '&lt;%', '?&gt;', '%&gt;');

			$str = preg_replace($patterns, $replacements, $str);
		}

		$blade = new BladeCompiler($this->files, 'theme');

		$parsed = $blade->compileString($str);

		// Merge with shared data.
		$data = array_merge($this->view->getShared(), $data);

		ob_start() and extract($data, EXTR_SKIP);

		try
		{
			eval('?>'.$parsed);
		}
		catch (\Exception $e)
		{
			ob_end_clean(); throw $e;
		}

		$str = ob_get_contents();
		ob_end_clean();

		return $str;
	}

	/**
	 * Compile blade without PHP.
	 *
	 * @param  string $str
	 * @param  array  $data
	 * @return string
	 */
	public function bladerWithOutServerScript($str, $data = array())
	{
		return $this->blader($str, $data, false);
	}

	/**
	 * Compile twig.
	 *
	 * @param  string $str
	 * @param  array  $data
	 * @return string
	 */
	public function twigy($str, $data = array())
	{
		$twig = new TwigCompiler($this->view);

		return $twig->setData($data)->compileString($str);
	}

	/**
	 * Render a region.
	 *
	 * @param  string $region
	 * @return string
	 */
	public function place($region)
	{
		if (isset($this->regions[$region]))
		{
			return $this->regions[$region];
		}
	}

	/**
	 * Return asset instance.
	 *
	 * @return Asset
	 */
	public function asset()
	{
		return $this->asset;
	}

	/**
	 * Set up a content to template.
	 *
	 * @param  string $view
	 * @param  array  $args
	 * @return string
	 */
	public function of($view, $args = array(), $viewAs = null)
	{
		// Add theme location to view paths.
		$this->view->addLocation(public_path().'/'.$this->path());

		// Layout.
		$layout = ucfirst($this->layout);

		// Fire event before render theme.
		$this->fire('beforeRenderTheme.'.$this->theme, $this);

		// Fire event before render layout.
		$this->fire('beforeRenderLayout.'.$layout, $this);

		// Fire event after theme and layout is set.
		$this->fire('beforeRenderThemeWithLayout.'.$this->theme.$layout, $this);

		// Compile string blade, string twig, or from file path.
		switch ($viewAs)
		{
			case 'blade' :
				$content = $this->bladerWithOutServerScript($view, $args);
				break;
			case 'twig' :
				$content = $this->twigy($view, $args);
				break;
			default :
				$content = $this->view->make($view, $args)->render();
				break;
		}

		// Set up a content regional.
		$this->regions['content'] = $content;

		return $this;
	}

	/**
	 * Container view.
	 *
	 * Using a container module view inside a theme, this is
	 * useful when you separate a view inside a theme.
	 *
	 * @param  string $view
	 * @param  array  $args
	 * @return Theme
	 */
	public function scope($view, $args = array())
	{
		$viewDir = $this->getConfig('containerDir.view');

		$view = $viewDir.'.'.$view;

		return $this->of($view, $args);
	}

	/**
	 * Compile from string.
	 *
	 * @param  string $view
	 * @param  array  $args
	 * @param  string $type
	 * @return Theme
	 */
	public function string($str, $args, $type = 'blade')
	{
		return $this->of($str, $args, $type);
	}

	/**
	 * Set cookie to response.
	 *
	 * @param  Cookie $cookie
	 * @return Theme
	 */
	public function withCookie(Cookie $cookie)
	{
		$this->cookie = $cookie;

		return $this;
	}

	/**
	 * Return a template with content.
	 *
	 * @return string
	 */
	public function render()
	{
		// Fire the event before render.
		$this->fire('after', $this);

		$layoutDir = $this->getConfig('containerDir.layout');

		$content = '';

		if ($this->view->exists($layoutDir.'.'.$this->layout))
		{
			$content = $this->view->make($layoutDir.'.'.$this->layout)->render();
		}

		// Having cookie set.
		if ($this->cookie)
		{
			$content = new Response($content);

			$content->withCookie($this->cookie);
		}

		return $content;
	}

	/**
	 * Magic method to set or append region.
	 *
	 * Set and append region separate by upper alpha,
	 * to set a region or append you can code like below:
	 *
	 * $theme->setTitle or $thtme->setAnything
	 * $theme->appendTitle
	 *
	 * @param  string $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters = array())
	{
		$callable = preg_split('|[A-Z]|', $method);

		if (in_array($callable[0], array('set', 'append')))
		{
			$value = strtolower(preg_replace('|^'.$callable[0].'|', '', $method));

			array_unshift($parameters, $value);

			return call_user_func_array(array($this, $callable[0]), $parameters);
		}
	}

}