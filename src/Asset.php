<?php namespace Teepluss\Theme;

use Closure;
use Illuminate\Support\Facades\URL;

class Asset {

	/**
	 * Path to assets.
	 *
	 * @var string
	 */
	public static $path;

	/**
	 * All of the instantiated asset containers.
	 *
	 * @var array
	 */
	public static $containers = array();

	/**
	 * Asset buffering.
	 *
	 * @var array
	 */
	protected $stacks = array(
		'cooks'  => array(),
		'serves' => array()
	);


	/**
	 * Asset construct.
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Add a path to theme.
	 *
	 * @param string $path
	 */
	public function addPath($path)
	{
		static::$path = rtrim($path, '/').'/';
	}

	/**
	 * Get an asset container instance.
	 *
	 * <code>
	 *		// Get the default asset container
	 *		$container = Asset::container();
	 *
	 *		// Get a named asset container
	 *		$container = Asset::container('footer');
	 * </code>
	 *
	 * @param  string            $container
	 * @return AssetContainer
	 */
	public static function container($container = 'default')
	{
		if ( ! isset(static::$containers[$container]))
		{
			static::$containers[$container] = new AssetContainer($container);
		}

		return static::$containers[$container];
	}

	/**
	 * Cooking your assets.
	 *
	 * @param  string  $name
	 * @param  Closure $callbacks
	 * @return void
	 */
	public function cook($name, Closure $callbacks)
	{
		$this->stacks['cooks'][$name] = $callbacks;
	}

	/**
	 * Serve asset preparing from cook.
	 *
	 * @param  string $name
	 * @return Asset
	 */
	public function serve($name)
	{
		$this->stacks['serves'][$name] = true;

		return $this;
	}

	/**
	 * Flush all cooks.
	 *
	 * @return void
	 */
	public function flush()
	{
		foreach ($this->stacks['serves'] as $key => $val)
		{
			if (array_key_exists($key, $this->stacks['cooks']))
			{
				$callback = $this->stacks['cooks'][$key];

				if ($callback instanceof Closure)
				{
					$callback($this);
				}
			}
		}
	}

	/**
	 * Magic Method for calling methods on the default container.
	 *
	 * <code>
	 *		// Call the "styles" method on the default container
	 *		echo Asset::styles();
	 *
	 *		// Call the "add" method on the default container
	 *		Asset::add('jquery', 'js/jquery.js');
	 * </code>
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array(static::container(), $method), $parameters);
	}

}