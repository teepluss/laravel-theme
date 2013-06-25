<?php namespace Teepluss\Theme;

class Breadcrumb {

    /**
     * Crumbs
     *
     * @var array
     */
    public $crumbs = array();

    /**
     * Add breadcrumb to array.
     *
     * @param  mixed  $label
     * @param  string $url
     * @return Breadcrumb
     */
    public function add($label, $url='')
    {
        if (is_array($label))
        {
            if (count($label) > 0) foreach ($label as $crumb)
            {
                $defaults = array(
                    'label' => '',
                    'url'   => ''
                );
                $crumb = array_merge($defaults, $crumb);

                $this->add($crumb['label'], $crumb['url']);
            }
        }
        else
        {
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
     * Render breadcrumbs.
     *
     * @return string
     */
    public function render()
    {
        $crumbs = $this->getCrumbs();
        $output = '<ul class="breadcrumb">';

        if (count($crumbs) > 0) foreach ($crumbs as $i => $crumb)
        {
            $label = $crumb['label'];

            $label = trim(strip_tags($label));

            if ($i != (count($crumbs) - 1))
            {
                $url = $crumb['url'];

                $output .= '<li>';
                $output .= '<a href="'.$url.'">'.$label.'</a><span class="divider">/</span>';
                $output .= '</li>'."\n";
            }
            else
            {
                $output .= '<li class="active">';
                $output .= $label;
                $output .= '</li>'."\n";
            }
        }
        $output .= '</ul>';

        return $output;
    }

}