## Theme Management for Laravel

### For Laravel 4, please use the [v1.x branch](https://github.com/teepluss/laravel-theme/tree/v1.x)!

Theme is a theme management for Laravel 5, it is the easiest way to organize your skins, layouts and assets.
Right now Theme supports PHP, Blade, and Twig.

### Installation

- [Theme on Packagist](https://packagist.org/packages/teepluss/theme)
- [Theme on GitHub](https://github.com/teepluss/laravel-theme)

To get the latest version of Theme simply require it in your `composer.json` file.

~~~
"teepluss/theme": "dev-master"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `config/app.php` and find the `providers` key.

~~~
'providers' => [

    'Teepluss\Theme\ThemeServiceProvider',

]
~~~

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `config/app.php` file.

~~~
'aliases' => [

    'Theme' => 'Teepluss\Theme\Facades\Theme',

]
~~~

Publish config using artisan CLI.

~~~
php artisan vendor:publish --provider="Teepluss\Theme\ThemeServiceProvider"
~~~

## Usage

Theme has many features to help you get started with Laravel

- [Create theme with artisan CLI](#create-theme-with-artisan-cli)
- [Configuration](#configuration)
- [Basic usage](#basic-usage)
- [Compiler](#compiler)
- [Render from string](#render-from-string)
- [Compile string](#compile-string)
- [Symlink from another view](#symlink-from-another-view)
- [Basic usage of assets](#basic-usage-of-assets)
- [Preparing group of assets](#preparing-group-of-assets)
- [Partials](#partials)
- [Working with regions](#working-with-regions)
- [Preparing data to view](#preparing-data-to-view)
- [Breadcrumb](#breadcrumb)
- [Widgets design structure](#widgets-design-structure)
- [Using theme global](#using-theme-global)

### Create theme with artisan CLI

The first time you have to create theme "default" structure, using the artisan command:

~~~
php artisan theme:create default
~~~
> If you change the facade name you can add an option --facade="Alias".

To delete an existing theme, use the command:

~~~
php artisan theme:destroy default
~~~

> the type can be php, blade and twig.

Create from the applicaton without CLI.

~~~php
Artisan::call('theme:create', ['name' => 'foo', '--type' => 'blade']);
~~~

### Configuration

After the config is published, you will see the config file in "config/theme", but all the configuration can be replaced by a config file inside a theme.

> Theme config location: /public/themes/[theme]/config.php

The config is convenient for setting up basic CSS/JS, partial composer, breadcrumb template and also metas.

Example:
~~~php
'events' => array(

    // Before event inherit from package config and the theme that call before,
    // you can use this event to set meta, breadcrumb template or anything
    // you want inheriting.
    'before' => function($theme)
    {
        // You can remove this line anytime.
        $theme->setTitle('Copyright ©  2013 - Laravel.in.th');

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
    },

    // Listen on event before render a theme,
    // this event should call to assign some assets,
    // breadcrumb template.
    'beforeRenderTheme' => function($theme)
    {
        // You may use this event to set up your assets.
        // $theme->asset()->usePath()->add('core', 'core.js');
        // $theme->asset()->add('jquery', 'vendor/jquery/jquery.min.js');
        // $theme->asset()->add('jquery-ui', 'vendor/jqueryui/jquery-ui.min.js', array('jquery'));


        // $theme->partialComposer('header', function($view)
        // {
        //     $view->with('auth', Auth::user());
        // });
    },

    // Listen on event before render a layout,
    // this should call to assign style, script for a layout.
    'beforeRenderLayout' => array(

        'default' => function($theme)
        {
            // $theme->asset()->usePath()->add('ipad', 'css/layouts/ipad.css');
        }

    )

)
~~~

### Basic usage

~~~php
namespace App\Http\Controllers;

use Theme;

class HomeController extends Controller {

    public function getIndex()
    {
        $theme = Theme::uses('default')->layout('mobile');

        $view = array(
            'name' => 'Teepluss'
        );

        // home.index will look up the path 'resources/views/home/index.php'
        return $theme->of('home.index', $view)->render();

        // Specific status code with render.
        return $theme->of('home.index', $view)->render(200);

        // home.index will look up the path 'resources/views/mobile/home/index.php'
        return $theme->ofWithLayout('home.index', $view)->render();

        // home.index will look up the path 'public/themes/default/views/home/index.php'
        return $theme->scope('home.index', $view)->render();

        // home.index will look up the path 'public/themes/default/views/mobile/home/index.php'
        return $theme->scopeWithLayout('home.index', $view)->render();

        // Looking for a custom path.
        return $theme->load('app.somewhere.viewfile', $view)->render();

        // Working with cookie.
        $cookie = Cookie::make('name', 'Tee');
        return $theme->of('home.index', $view)->withCookie($cookie)->render();
    }

}
~~~
> Get only content "$theme->of('home.index')->content();".

Finding from both theme's view and application's view.
~~~php
$theme = Theme::uses('default')->layout('default');

return $theme->watch('home.index')->render();
~~~

To check whether a theme exists.

~~~php
// Returns boolean.
Theme::exists('themename');
~~~

To find the location of a view.

~~~php
$which = $theme->scope('home.index')->location();

echo $which; // themer::views.home.index

$which = $theme->scope('home.index')->location(true);

echo $which; // ./public/themes/name/views/home/index.blade.php
~~~

### Compiler

Theme now supports PHP, Blade and Twig. To use Blade or Twig template you just create a file with extension
~~~
[file].blade.php or [file].twig.php
~~~

### Render from string.

~~~php
// Blade template.
return $theme->string('<h1>{{ $name }}</h1>', array('name' => 'Teepluss'), 'blade')->render();

// Twig Template
return $theme->string('<h1>{{ name }}</h1>', array('name' => 'Teepluss'), 'twig')->render();
~~~

### Compile string

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

### Symlink from another view

This is a nice feature when you have multiple files that have the same name, but need to be located as a separate one.

~~~php
// Theme A : /public/themes/a/views/welcome.blade.php

// Theme B : /public/themes/b/views/welcome.blade.php

// File welcome.blade.php at Theme B is the same as Theme A, so you can do link below:

// ................

// Location: public/themes/b/views/welcome.blade.php
Theme::symlink('a');

// That's it!
~~~

### Basic usage of assets

Add assets in your route.

~~~php
// path: public/css/style.css
$theme->asset()->add('core-style', 'css/style.css');

// path: public/js/script.css
$theme->asset()->container('footer')->add('core-script', 'js/script.js');

// path: public/themes/[current theme]/assets/css/custom.css
// This case has dependency with "core-style".
$theme->asset()->usePath()->add('custom', 'css/custom.css', array('core-style'));

// path: public/themes/[current theme]/assets/js/custom.js
// This case has dependency with "core-script".
$theme->asset()->container('footer')->usePath()->add('custom', 'js/custom.js', array('core-script'));
~~~
> You can force use theme to look up existing theme by passing parameter to method:
> $theme->asset()->usePath('default')

Writing in-line style or script.

~~~php

// Dependency with.
$dependencies = array();

// Writing an in-line script.
$theme->asset()->writeScript('inline-script', '
    $(function() {
        console.log("Running");
    })
', $dependencies);

// Writing an in-line style.
$theme->asset()->writeStyle('inline-style', '
    h1 { font-size: 0.9em; }
', $dependencies);

// Writing an in-line script, style without tag wrapper.
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

### Preparing group of assets.

Some assets you don't want to add on a page right now, but you still need them sometimes, so "cook" and "serve" is your magic.

Cook your assets.
~~~php
Theme::asset()->cook('backbone', function($asset)
{
    $asset->add('backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js');
    $asset->add('underscorejs', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js');
});
~~~

You can prepare on a global in package config.

~~~php
// Location: config/theme/config.php
....
    'events' => array(

        ....

        // This event will fire as a global you can add any assets you want here.
        'asset' => function($asset)
        {
            // Preparing asset you need to serve after.
            $asset->cook('backbone', function($asset)
            {
                $asset->add('backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js');
                $asset->add('underscorejs', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js');
            });
        }

    )
....
~~~

Serve theme when you need.
~~~php
// At the controller.
Theme::asset()->serve('backbone');
~~~

Then you can get output.
~~~html
...
<head>
    <?php echo Theme::asset()->scripts(); ?>
    <?php echo Theme::asset()->styles(); ?>
    <?php echo Theme::asset()->container('YOUR_CONTAINER')->scripts(); ?>
    <?php echo Theme::asset()->container('YOUR_CONTAINER')->styles(); ?>
</head>
...
~~~

### Partials

Render a partial in your layouts or views.

~~~php
// This will look up to "public/themes/[theme]/partials/header.php"
echo Theme::partial('header', array('title' => 'Header'));

// Partial with current layout specific.
// This will look up up to "public/themes/[theme]/partials/[CURRENT_LAYOUT]/header.php"
echo Theme::partialWithLayout('header', array('title' => 'Header'));
~~~

Finding from both theme's partial and application's partials.

~~~php
echo Theme::watchPartial('header', array('title' => 'Header'));
~~~

Partial composer.

~~~php
$theme->partialComposer('header', function($view)
{
    $view->with('key', 'value');
});

// Working with partialWithLayout.
$theme->partialComposer('header', function($view)
{
    $view->with('key', 'value');
}, 'layout-name');
~~~

### Working with regions.

Theme has magic methods to set, prepend and append anything.

~~~php
$theme->setTitle('Your title');

$theme->appendTitle('Your appended title');

$theme->prependTitle('Hello: ....');

$theme->setAnything('anything');

$theme->setFoo('foo');

// or

$theme->set('foo', 'foo');
~~~

Render in your layout or view.

~~~php
Theme::getAnything();

Theme::getFoo();

// or use place.

Theme::place('anything');

Theme::place('foo', 'default-value-if-it-does-not-exist');

// or

Theme::get('foo');
~~~

Check if the place exists or not.

~~~php
<?php if (Theme::has('title')) : ?>
    <?php echo Theme::place('title'); ?>
<?php endif; ?>

// or

<?php if (Theme::hasTitle()) : ?>
    <?php echo Theme::getTitle(); ?>
<?php endif; ?>
~~~

Get argument assigned to content in layout or region.

~~~php
Theme::getContentArguments();

// or

Theme::getContentArgument('name');

// To check if it exists

Theme::hasContentArgument('name');
~~~

> Theme::place('content') is a reserve region to render sub-view.

### Preparing data to view

Sometimes you don't need to execute heavy processing, so you can prepare and use when you need it.

~~~php
$theme->bind('something', function()
{
    return 'This is bound parameter.';
});
~~~

Using bound data on view.

~~~php
echo Theme::bind('something');
~~~

### Breadcrumb

In order to use breadcrumbs, follow the instruction below:

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

To render breadcrumbs.

~~~php
echo $theme->breadcrumb()->render();

// or

echo Theme::breadcrumb()->render();
~~~

You can set up breadcrumbs template anywhere you want by using a blade template.

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

Theme has many useful features called "widget" that can be anything.

### Creating a widget

You can create a widget class using artisan command:

Creating as a global.
~~~
php artisan theme:widget demo --global --type=blade
~~~
> Widget tpl is located in "resources/views/widgets/{widget-tpl}.{extension}"

Creating a specific theme name.
~~~
php artisan theme:widget demo default --type=blade
~~~
> Widget tpl is located in "public/themes/[theme]/widgets/{widget-tpl}.{extension}"

> The file name can be demo.php, demo.blade.php or demo.twig.php

Now you will see a widget class at /app/Widgets/WidgetDemo.php

~~~html
<h1>User Id: {{ $label }}</h1>
~~~

### Calling your widget in layout or view

~~~php
echo Theme::widget('demo', array('label' => 'Demo Widget'))->render();
~~~

### Using theme global
~~~php
use Teepluss\Theme\Contracts\Theme;
use App\Http\Controllers\Controller;

class BaseController extends Controller {

    /**
     * Theme instance.
     *
     * @var \Teepluss\Theme\Theme
     */
    protected $theme;

    /**
     * Construct
     *
     * @return void
     */
    public function __construct(Theme $theme)
    {
        // Using theme as a global.
        $this->theme = $theme->uses('default')->layout('ipad');
    }

}
~~~

To override theme or layout.
~~~php
public function getIndex()
{
    $this->theme->uses('newone');

    // or just override layout
    $this->theme->layout('desktop');

    $this->theme->of('somewhere.index')->render();
}
~~~

## Support or Contact

If you have any problems, Contact teepluss@gmail.com


[![Support via PayPal](https://rawgithub.com/chris---/Donation-Badges/master/paypal.jpeg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9GEC8J7FAG6JA)
