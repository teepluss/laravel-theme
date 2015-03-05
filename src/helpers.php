<?php

if ( ! function_exists('theme'))
{
    /**
     * Get the theme instance.
     *
     * @param  string  $themeName
     * @param  string  $layoutName
     * @return \Teepluss\Theme\Theme
     */
    function theme($themeName = null, $layoutName = null)
    {
        $theme = app('theme');

        if ($themeName)
        {
            $theme->theme($themeName);
        }

        if ($layoutName)
        {
            $theme->layout($layoutName);
        }

        return $theme;
    }
}