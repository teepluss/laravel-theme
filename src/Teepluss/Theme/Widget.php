<?php namespace Teepluss\Theme;

use Closure;
use Illuminate\View\Environment;
use Illuminate\Config\Repository;

abstract class Widget {

    /**
     * Theme instanced.
     *
     * @var Theme;
     */
    protected $theme;

    /**
     * Repository config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Environment view.
     *
     * @var \Illuminate\View\Environment
     */
    protected $view;

    protected $watch;

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
     * @param  string                        $theme
     * @param  \Illuminate\Config\Repository $view
     * @param  \Illuminate\View\Environment  $config
     * @return void
     */
    public function __construct(Theme $theme, Repository $config, Environment $view)
    {
        // Theme name.
        $this->theme = $theme;

        $this->config = $config;

        $this->view = $view;
    }

    /**
     * Abstract class init for a widget factory.
     *
     * @return void
     */
    //abstract public function init();

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
     * Set attribute.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
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
        $this->init($this->theme);
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

    public function watch($bool = true)
    {
        $this->watch = $bool;

        return $this;
    }

    /**
     * Render widget to HTML.
     *
     * @return string
     */
    public function render()
    {
        $widgetDir = $this->config->get('theme::containerDir.widget');

        $path = $this->theme->getThemeNamespace($widgetDir.'.'.$this->template);

        // If not found in theme widgets directory, try to watch in views/widgets again.
        if ($this->watch === true and ! $this->view->exists($path))
        {
            $path = $widgetDir.'.'.$this->template;
        }

        // Error file not exists.
        if ( ! $this->view->exists($path))
        {
            throw new UnknownWidgetFileException("Widget view [$this->template] not found.");
        }

        $widget = $this->view->make($path, $this->data)->render();

        return $widget;
    }

}