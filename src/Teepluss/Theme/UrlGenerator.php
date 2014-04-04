<?php namespace Teepluss\Theme;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class UrlGenerator extends BaseUrlGenerator {

    /**
     * Asset URL.
     *
     * @var string
     */
    protected $assetUrl = null;

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
            $this->assetUrl = \Config::get('theme::assetUrl', '');
        }

        // Using asset url, if available.
        if ($this->assetUrl)
        {
            $base = rtrim($this->assetUrl, '/');

            return $this->removeIndex($base).'/'.trim($path, '/');
        }

        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $root = $this->getRootUrl($this->getScheme($secure));

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

}