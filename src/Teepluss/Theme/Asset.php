<?php namespace Teepluss\Theme;

use Closure;
use Illuminate\Events\Dispatcher;
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
	 * All of the instantiated asset queues.
	 *
	 * @var array
	 */
	public static $queues = array();

	/**
	 * Asset construct.
	 *
	 * @param \Illuminate\Events\Dispatcher $events
	 */
	public function __construct(Dispatcher $events)
	{
		$this->events = $events;

		$that = $this;

		// Register events.
		$this->events->listen('asset.serve', function($name) use ($that)
		{
			$that->events->fire($name, array($that));
			$that->events->forget($name);
		});
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
     * Queue asset to compress.
     *
     * @param  string $queue
     * @param  Closure $assets
     * @return AssetQueue
     */
	public static function queue($queue, Closure $assets = null)
	{
		if ( ! isset(static::$queues[$queue]))
		{
			static::$queues[$queue] = new AssetQueue($queue, $assets);
		}

		return static::$queues[$queue];
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
		$this->events->listen('asset.cook.'.$name, $callbacks);
	}

	/**
	 * Serve asset preparing from cook.
	 *
	 * @param  string $name
	 * @return Asset
	 */
	public function serve($name)
	{
		$name = 'asset.cook.'.$name;

		$this->events->queue('asset.serve', array($name));

		return $this;
	}

	/**
	 * Flush all cooks.
	 *
	 * @return void
	 */
	public function flush()
	{
		// Flush asset that need to serve.
		$this->events->flush('asset.serve');
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