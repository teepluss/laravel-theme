<?php namespace Teepluss\Theme;

use Illuminate\Config\Repository;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class UrlGenerator extends BaseUrlGenerator {

    /**
     * Repository config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Asset URL.
     *
     * @var string
     */
    protected $assetUrl = null;

    /**
     * Create a new URL Generator instance.
     *
     * @param  \Illuminate\Routing\RouteCollection  $routes
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @param  \Illuminate\Config\Repository $config
     * @return void
     */
    public function __construct($routes, Request $request, Repository $config)
    {
        $this->routes = $routes;

        $this->setRequest($request);

        $this->config = $config;
    }

    /**
     * Generate a URL to an application asset.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) return $path;

        // Finding asset url config.
        if (is_null($this->assetUrl))
        {
            $this->assetUrl = $this->config->get('theme::assetUrl', '');
        }

        // Using asset url, if available.
        if ($this->assetUrl)
        {
            return rtrim($this->assetUrl, '/').'/'.trim($path, '/');
        }

        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $root = $this->getRootUrl($this->getScheme($secure));

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

}