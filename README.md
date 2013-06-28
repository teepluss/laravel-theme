## Theme for Laravel 4

Theme is a theme management for Laravel version 4, this is easier way to organize your skin, layout and asset,
right now Theme is support PHP, Blade, and Twig.

### Installation

- [Theme on Packagist](https://packagist.org/packages/teepluss/theme)
- [Theme on GitHub](https://github.com/teepluss/laravel4-theme)

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"teepluss/theme": "dev-master"
~~~

To get nightly builds for developers.

~~~php
"teepluss/theme": "dev-develop"
~~~
> The develop branch are interim builds that are untested and unsupported . Use at your own risk!

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

## Usage

Theme has mamy features to help you get start with Laravel 4

- [Creating with artisan](#create-theme-with-artisan)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Compiler](#compiler)
- [Compile from string](#compile-from-string)
- [Compile on the fly](#compile-on-the-fly)
- [Manage Assets](#manage-assets)
- [Asset Compression](#compress-assets-using-queue)
- [Partials](#partials)
- [Set and Append](#set-and-append)
- [Binding parameter to view](#binding-parameter-to-view)
- [Breadcrumb](#breadcrumb)
- [Widgets Design Structure](#widgets-design-structure)

### Create theme with artisan

First time you have to create theme "default" structure using artisan command:

~~~
php artisan theme:create default --type=blade
~~~

To delete exsisting theme using command:

~~~
php artisan theme:destroy default
~~~

> type can be php, blade and twig.

### Configuration

After config published you will see the config file at "app/config/packages/teepluss/theme", but all configuration can be overrided
by config inside a theme.

> Theme config location: /public/themes/[theme]/config.php

The config is convenient for set up basic CSS/JS, partial composer, breadcrumb template and metas also.

Example:
~~~php
'events' => array(

    // Listen on event before render theme.
    'beforeRenderTheme' => function($theme)
    {
        // You may use this event to set up your assets.
        //$theme->asset()->usePath()->add('core', 'core.js');
        //$theme->asset()->add('jquery', 'vendor/jquery/jquery.min.js');
        //$theme->asset()->add('jquery-ui', 'vendor/jqueryui/jquery-ui.min.js', array('jquery'));


        // Breadcrumb template.
        // $theme->breadcrumb()->setTemplate('
        //     <ul class="breadcrumb">
        //     @foreach ($crumbs as $i => $crumb)
        //         @if ($i != (count($crumbs) - 1))
        //         <li><a href="{{ $crumb["url"] }}">{{ $crumb["label"] }}</a><span class="divider">/</span></li>
        //         @else
        //         <li class="active">{{ $crumb["label"] }}</li>
        //         @endif
        //     @endforeach
        //     </ul>
        // ');


        // $theme->partialComposer('header', function($view)
        // {
        //     $view->with('auth', Auth::user());
        // });
    },

    'beforeRenderLayout' => array(

        'default' => function($theme)
        {

        }

    )

)
~~~

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

To find location of view inside a theme.

~~~php
$which = $theme->which('home.index');

echo $which; // theme::views.home.index
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

// Writing an inline script, style without tag wrapper.
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

### Compress assets using queue

Theme asset having feature to compress assets by using queue.

~~~php
// To queue asset outside theme path.
$theme->asset()->queue('queue-name')->add('one', 'js/one.js');
$theme->asset()->queue('queue-name')->add('two', 'js/two.js');

// To queue asset inside theme path.
$theme->asset()->queue('queue-name')->usePath()->add('xone', 'js/one.js');
$theme->asset()->queue('queue-name')->usePath()->add('xtwo', 'js/two.js');
~~~

To render compressed assets inside view.

~~~php
echo Theme::asset()->queue('queue-name')->scripts(array('defer' => 'defer'));
echo Theme::asset()->queue('queue-name')->styles(array('async' => 'async'));
~~~

To force compress.

~~~php
$theme->asset()->queue('queue-name')->compress();
~~~

> If you already publish config before this feature available, you need to re-publish config again.

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

Check the place is existing or not.

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

### Breadcrumb

To using breadcrumb follow the instruction below:

~~~php
$theme->breadcrumb()->add('label', 'http://...')->add('label2', 'http:...');

// or

$theme->breadcrumb()->add(array(
    array(
        'label' => 'label1',
        'url'   => 'http://...'
    ),
    array(
        'label' => 'label2',
        'url'   => 'http://...'
    )
));
~~~

To render breadcrumb.

~~~php
echo $theme->breadcrumb()->render();

// or

echo Theme::breadcrumb()->render();
~~~

You can set up breadcrumb template anywhere you want by using blade template.

~~~php
$theme->breadcrumb()->setTemplate('
    <ul class="breadcrumb">
    @foreach ($crumbs as $i => $crumb)
        @if ($i != (count($crumbs) - 1))
        <li><a href="{{ $crumb["url"] }}">{{ $crumb["label"] }}</a><span class="divider">/</span></li>
        @else
        <li class="active">{{ $crumb["label"] }}</li>
        @endif
    @endforeach
    </ul>
');
~~~

## Widgets Design Structure

Theme have many useful features the one call "widget" that can be anything.

### Creating a widget

You can create a widget class using artisan command:

~~~
php artisan theme:widget demo default --type=blade
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
