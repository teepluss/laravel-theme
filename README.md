## Theme for Laravel 4

Theme is a theme management for Laravel version 4, this is easier way to organize your skin and theme,
Right now Theme is support PHP, Blade, and Twig.

> To current user who want to use twig feature, you need to run artisan to publish config again.
~~~
php artisan config:publish teepluss/theme
~~~

### Installation

- [Theme on Packagist](https://packagist.org/packages/teepluss/theme)
- [Theme on GitHub](https://github.com/teepluss/laravel4-theme)

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"teepluss/theme": "dev-master"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `app/config/app.php` and find the `providers` key.

~~~
'providers' => array(

    'Teepluss\Theme\ThemeServiceProvider'

)
~~~

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(

    'Theme' => 'Teepluss\Theme\Facades\Theme'

)
~~~

Publish config using artisan CLI.

~~~
php artisan config:publish teepluss/theme
~~~

## Document

First time you have to create theme "default" structure using artisan command:

~~~
php artisan theme:create default --type=blade
~~~

> type can be php, blade and twig.

### Basic Usage

~~~php
class HomeController extends BaseController {

    public function getIndex()
    {
        $theme = Theme::uses('default')->layout('default');

        $view = array(
            'name' => 'Teepluss'
        );

        // home.index will look up the path 'app/views/home/index.php'
        return $theme->of('home.index', $view)->render();


        // home.index will look up the path 'public/themes/default/views/home/index.php'
        //return $theme->scope('home.index', $view)->render();

        // Working with cookie
        //$cookie = Cookie::make('name', 'Tee');
        //return $theme->of('home.index', $view)->withCookie($cookie)->render();
    }

}
~~~

### Compiler

Theme is now support PHP, Blade and Twig. To using Blade or Twig template you just create a file with extension
~~~
[file].blade.php or [file].twig.php
~~~

### Compile from string

~~~php
// Blade template.
return $theme->string('<h1>{{ $name }}</h1>', array('name' => 'Teepluss'), 'blade')->render();

// Twig Template
return $theme->string('<h1>{{ name }}</h1>', array('name' => 'Teepluss'), 'twig')->render();
~~~

### Compile on the fly

~~~php
// Blade compile.
$template = '<h1>Name: {{ $name }}</h1><p>{{ Theme::widget("WidgetIntro", array("userId" => 9999, "title" => "Demo Widget"))->render() }}</p>';

echo Theme::blader($template, array('name' => 'Teepluss'));
~~~

~~~php
// Twig compile.
$template = '<h1>Name: {{ name }}</h1><p>{{ Theme.widget("WidgetIntro", {"userId" : 9999, "title" : "Demo Widget"}).render() }}</p>';

echo Theme::twigy($template, array('name' => 'Teepluss'));
~~~

### Manage Assets

Add assets in your controller.

~~~php
// path: public/css/style.css
$theme->asset()->add('core-style', 'css/style.css');

// path: public/js/script.css
$theme->asset()->container('footer')->add('core-script', 'js/script.js');

// path: public/themes/[current theme]/css/custom.css
// This case has dependency with "core-style".
$theme->asset()->usePath()->add('custom', 'css/custom.css', array('core-style'));

// path: public/themes/[current theme]/js/custom.js
// This case has dependency with "core-script".
$theme->asset()->container('footer')->usePath()->add('custom', 'js/custom.js', array('core-script'));
~~~

Writing inline style or script.

~~~php

// Dependency with.
$dependencies = array();

// Writing an inline script.
$theme->asset()->writeScript('inline-script', '
    $(function() {
        console.log("Running");
    })
', $dependencies);

// Writing an inline style.
$theme->asset()->writeStyle('inline-style', '
    h1 { font-size: 0.9em; }
', $dependencies);

// Writing an inline script without tag wrapper.
$theme->asset()->writeContent('custom-inline-script', '
    <script>
        $(function() {
            console.log("Running");
        });
    </script>
', $dependencies);
~~~

Render styles and scripts in your layout.

~~~php
// Without container
echo Theme::asset()->styles();

// With "footer" container
echo Theme::asset()->container('footer')->scripts();
~~~

Direct path to theme asset.

~~~php
echo Theme::asset()->url('img/image.png');
~~~

### Partials

Render a partial in your layout or views.

~~~php
// This will look up to "public/themes/[theme]/partials/header.php"
echo Theme::partial('header', array('title' => 'Header'));
~~~

Partial composer.

~~~php
$theme->partialComposer('header', function($view)
{
    $view->with('key', 'value');
});
~~~

### Set and Append

Theme have magic methods to set or append anything.

~~~php
$theme->setTitle('Your title');

$theme->appendTitle('Your append title');

$theme->setAnything('anything');

$theme->setFoo('foo');
~~~

Render in your layout or view.

~~~php
Theme::place('title');

Theme::place('anything');

Theme::place('foo', 'default-value-on-not-exists');
~~~

Check the place is exists.

~~~php
<?php if (Theme::has('title')) : ?>
    <?php echo Theme::place('title'); ?>
<?php endif; ?>
~~~

> Theme::place('content') is a reserve region to render sub-view.

### Binding parameter to view

~~~php
$theme->bind('something', function()
{
    return 'This is binding parameter.';
});

return $this->string('<h1>{{ $something }}</h1>', array(), 'blade');
~~~

### Configuration

After your published config file you will see the config at "app/config/packages/teepluss/theme/config.php"

### Main configuration for theme package

~~~php
return array(

    /*
    |--------------------------------------------------------------------------
    | Theme Default
    |--------------------------------------------------------------------------
    |
    | If you don't set a theme when using a "Theme" class the default theme
    | will replace automatically.
    |
    */

    'themeDefault' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Layout Default
    |--------------------------------------------------------------------------
    |
    | If you don't set a layout when using a "Theme" class the default layout
    | will replace automatically.
    |
    */

    'layoutDefault' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Path to lookup theme
    |--------------------------------------------------------------------------
    |
    | The root path contains themes collections.
    |
    */

    'themeDir' => 'themes',

    /*
    |--------------------------------------------------------------------------
    | A pieces of theme collections
    |--------------------------------------------------------------------------
    |
    | Inside a theme path we need to set up directories to
    | keep "layouts", "assets" and "partials".
    |
    */

    'containerDir' => array(
        'layout'  => 'layouts',
        'asset'   => 'assets',
        'partial' => 'partials',
        'widget'  => 'widgets',
        'view'    => 'views'
    ),

    /*
    |--------------------------------------------------------------------------
    | Listener from events
    |--------------------------------------------------------------------------
    |
    | You can hook a theme when event fired on activities
    | this is cool feature to set up a title, meta, default styles and scripts.
    |
    */

    'events' => array(

        // Before set up theme.
        'before' => function($theme)
        {

        },

        // After set up theme and layout, but before rendering.
        'after' => function($theme)
        {

        },

        // Listen on event set up theme.
        'onSetTheme' => array(

            'default' => function($theme)
            {
                $theme->setTitle('This is theme');
            },

            // 'asian' => function($theme)
            // {
            //  $theme->setTitle('Asian theme is set');
            // }

        ),

        // Listen on event set up layout.
        'onSetLayout' => array(

            'default' => function($theme)
            {
                $theme->setTitle('This is layout');
            },

            // 'ipad' => function($theme)
            // {
            //  $theme->setTitle('Layout summer is set')
            // }

        ),

        // Listen on event before render theme.
        'beforeRenderTheme' => array(

            'default' => function($theme)
            {
                // add css for theme
            },

            // 'asian' => function($theme)
            // {
            //  $theme->asset()->usePath()->add('style', 'css/..../style.css');
            // }

        ),

        // Listen on event before render layout.
        'beforeRenderLayout' => array(

            'default' => function($theme)
            {
                // add css for layout
            },

            // 'ipad' => function($theme)
            // {
            //  $theme->asset()->usePath()->add('ipad-layout', 'css/..../ipad.css');
            // }

        ),

        // Listen on event before render theme and layout
        'beforeRenderThemeWithLayout' => array(

            'defaultDefault' => function($theme)
            {
                $theme->setTitle('This is theme and layout');
            },

            // 'asianSummer' => function($theme)
            // {
            //  $theme->setTitle('Theme asian and layout summer is set');
            // }

        )

    )

);
~~~

### Configuration file inside a theme

After using CLI "php artisan theme:generate name" you will see a config file inside a theme, then you can set up events
like main package configuration, but remember the first priority is on package configuration, this mean config inside a theme
can be override by the package config.

> The configuration file contents all of events that you can add css/js bootstrap for any theme.

## Widgets Design Structure

Theme have many useful features the one call "widget" that can be anything.

### Creating a widget

You can create a widget class using artisan command:

~~~
php artisan theme:widget demo default
~~~

> First parameter is widget name, the second is theme name.

Now you will see a class at /app/widgets/WidgetDemo.php

### Creating widget view

Every widget need a view, for a class "WidgetDemo.php" you should create a view as below:

~~~
public/themes/[theme]/widgets/demo.blade.php
~~~

> The file name can be demo.php, demo.blade.php, demo.twig.php

~~~html
<h1>User Id: {{ $label }}</h1>
~~~

### Calling your widget in layout or view

~~~php
echo Theme::widget('WidgetDemo', array('label' => 'Demo Widget'))->render();
~~~

or you can call with shortly name leads with lower case.

~~~php
echo Theme::widget('demo', array('label' => 'Demo Widget'))->render();
~~~

## Support or Contact

If you have some problem, Contact teepluss@gmail.com
