<?php namespace Teepluss\Theme;

use Closure;

use Illuminate\Config\Repository;
use Illuminate\View\Environment;

abstract class Widget {

    /**
     * Repository config.
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

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
     * Widget file template.
     *
     * @var string
     */
    public $template;

    /**
     * Default attributes.
     *
     * @var array
     */
    public $attributes = array();

    /**
     * Create a new theme instance.
     *
     * @param  \Illuminate\Config\Repository  $view
     * @param  \Illuminate\View\Environment  $config
     * @param  Asset  $asset
     * @return void
     */
    public function __construct(Repository $config, Environment $view, Asset $asset)
    {
        $this->config = $config;

        $this->view = $view;

        $this->asset = $asset;
    }

    /**
     * Abstract class init for a widget factory.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Abstract class run for a widget factory.
     *
     * @return void
     */
    abstract public function run();

    /**
     * Set attributes to object var.
     *
     * @param  arary  $attributes
     * @return void
     */
    public function setAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get attribute with a key.
     *
     * @param  string  $key
     * @param  string  $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return array_get($this->attributes, $key, $default);
    }

    /**
     * Start widget factory.
     *
     * @return void
     */
    public function beginWidget()
    {
        $this->init();
    }

    /**
     * End widget factory.
     *
     * @return void
     */
    public function endWidget()
    {
        $data = (array) $this->run();

        $this->data = array_merge($this->attributes, $data);
    }

    /**
     * Render widget to HTML.
     *
     * @return string
     */
    public function render()
    {
        $widgetDir = $this->config->get('theme::containerDir.widget');

        $widget = '';

        if ($this->view->exists($widgetDir.'.'.$this->template))
        {
            $widget = $this->view->make($widgetDir.'.'.$this->template, $this->data)->render();
        }

        return $widget;
    }

    /**
     * To string magic method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}