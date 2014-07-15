<?php namespace Teepluss\Theme;

use Closure;
//use Illuminate\View\Factory;
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
     * @var \Illuminate\View\Factory
     */
    protected $view;

    /**
     * Widget file template.
     *
     * @var string
     */
    public $template;

    /**
     * Watching widget template.
     *
     * @var boolean
     */
    public $watch;

    /**
     * Default attributes.
     *
     * @var array
     */
    public $attributes = array();

    /**
     * Attributes including data.
     *
     * @var array
     */
    public $data = array();

    /**
     * Turn on/off widget.
     *
     * @var boolean
     */
    public $enable = true;

    /**
     * Create a new theme instance.
     *
     * @param Theme                         $theme
     * @param \Illuminate\Config\Repository $config
     * @param \Illuminate\View\Factory      $view
     * @return \Teepluss\Theme\Widget
     */
    public function __construct(Theme $theme, Repository $config, $view)
    {
        // Theme name.
        $this->theme = $theme;

        // Laravel config
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
     * Disble widget.
     *
     * @return void
     */
    protected function disable()
    {
        $this->enable = false;
    }

    /**
     * Start widget factory.
     *
     * @return void
     */
    public function beginWidget()
    {
        // Init widget when enable is true.
        if ($this->enable == true)
        {
            $this->init($this->theme);
        }
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
     * Watch widget tpl in theme, also app/views/widgets/ too.
     *
     * @param  boolean $bool
     * @return Widget
     */
    public function watch($bool = true)
    {
        $this->watch = $bool;

        return $this;
    }

    /**
     * Render widget to HTML.
     *
     * @throws UnknownWidgetFileException
     * @return string
     */
    public function render()
    {
        if ($this->enable == false)
        {
            return '';
        }

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