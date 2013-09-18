<?php namespace Teepluss\Theme;

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
	 * All of the instantiated asset queues.
	 *
	 * @var array
	 */
	public static $queues = array();

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
	 * Queue asset to compress.
	 *
	 * @param  string $queue
	 * @return AssetQueue
	 */
	public static function queue($queue)
	{
		if ( ! isset(static::$queues[$queue]))
		{
			static::$queues[$queue] = new AssetQueue($queue);
		}

		return static::$queues[$queue];
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