<?php namespace Teepluss\Theme;

use Illuminate\Support\Facades\URL;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

class Breadcrumb {
    /**
     * Template
     *
     * @var string
     */
    public $template = '';

    /**
     * Crumbs
     *
     * @var array
     */
    public $crumbs = array();

    /**
     * Filesystem.
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new breadcrumb instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     *
     * @return \Teepluss\Theme\Breadcrumb
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;

        // Template for breadcrumb.
        $this->template = '
            <ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                @foreach ($crumbs as $i => $crumb)
                @if ($i != (count($crumbs) - 1))
                <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<meta itemprop="position" content="{{ $i + 1}}" />
					<a href="{{ $crumb["url"] }}" itemprop="item" title="{{ $crumb["label"] }}">
						{!! $crumb["label"] !!}
						<meta itemprop="name" content="{{ $crumb["label"] }}" />
					</a>
					<span class="divider">/</span>
				</li>
                @else
                <li class="active">{!! $crumb["label"] !!}</li>
                @endif
                @endforeach
            </ul>
        ';
    }

    /**
     * Set template.
     *
     * @param string
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Add breadcrumb to array.
     *
     * @param  mixed  $label
     * @param  string $url
     * @return Breadcrumb
     */
    public function add($label, $url='')
    {
        if (is_array($label)) {
            if (count($label) > 0) foreach ($label as $crumb) {
                $defaults = [
                    'label' => '',
                    'url'   => ''
                ];
                $crumb = array_merge($defaults, $crumb);
                $this->add($crumb['label'], $crumb['url']);
            }
        } else {
            $label = trim(strip_tags($label, '<i><b><strong>'));
            if (! preg_match('|^http(s)?|', $url)) {
                $url = URL::to($url);
            }
            $this->crumbs[] = array('label' => $label, 'url' => $url);
        }

        return $this;
    }

    /**
     * Get crumbs.
     *
     * @return array
     */
    public function getCrumbs()
    {
        return $this->crumbs;
    }

    /**
     * Compile blade template to HTML.
     *
     * @param  string $template
     * @param  array $data
     *
     * @throws \Exception
     * @return string
     */
    public function compile($template, $data = array())
    {
        $compiler = new BladeCompiler($this->files, 'theme');

        // Get blade compiler.
        $parsed = $compiler->compileString($template);

        ob_start() and extract($data, EXTR_SKIP);
        try {
            eval('?>'.$parsed);
        } catch (\Exception $e) {
            ob_end_clean(); throw $e;
        }
        $template = ob_get_contents();
        ob_end_clean();

        return $template;
    }

    /**
     * Render breadcrumbs.
     *
     * @return string
     */
    public function render()
    {
        $crumbs = $this->getCrumbs();

        return $this->compile($this->template, compact('crumbs'));
    }

}