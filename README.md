## Theme for Laravel 4

Theme is a theme managment for Laravel version 4, this is easier way to organize your skin and theme.

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

    'Teepluss\ThemeServiceProvider'

)
~~~

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(

    'Theme' => 'Teepluss\Theme\Facades\Theme'

)
~~~

Execute the config publish command.

~~~
php artisan config:publish teepluss/theme
~~~

## Document

First time you have to create theme "default" structure using artisan command:

~~~
php artisan generate:theme default
~~~

or you can manually create like below:

~~~
public/themes/default/assets/js/
public/themes/default/assets/css/
public/themes/default/assets/img/
public/themes/default/layouts/
public/themes/default/partials/
public/themes/default/views/
public/themes/default/widgets/
~~~

### Creating first layout

file: `public/theme/default/layouts/default.php`

~~~html
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo Theme::place('title'); ?></title>
        <meta charset="utf-8">
        <?php echo Theme::asset()->styles(); ?>
        <?php echo Theme::asset()->scripts(); ?>
    </head>
    <body>
        <?php echo Theme::partial('header'); ?>

        <div id="mid-container" class="container">
            <?php echo Theme::place('content'); ?>
        </div>

        <?php echo Theme::partial('footer'); ?>

        <?php echo Theme::asset()->container('footer')->scripts(); ?>
    </body>
</html>
~~~


### Creating partial header and footer

file: `public/theme/default/partials/header.php`

~~~
<header>Header</header>
~~~

file: `public/theme/default/partials/footer.php`

~~~
<footer>Copyright bla bla bla</footer>
~~~


### Basic Usage

~~~php
class HomeController extends BaseController {

    public function getIndex()
    {
        $theme = Theme::theme('default')->layout('default');

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

### Manage Assets

Add assets in your conteoller.

~~~php
// path: public/css/style.css
$theme->asset()->add('css/style.css');

// path: public/js/script.css
$theme->asset()->container('footer')->add('js/script.js');

// path: public/themes/[current theme]/css/custom.css
$theme->asset()->usePath()->add('css/custom.css');

// path: public/themes/[current theme]/js/custom.js
$theme->asset()->container('footer')->usePath()->add('js/custom.js');
~~~

Render styles and scripts in your layout.

~~~php
// Without container
echo Theme::asset()->styles();

// With "footer" container
echo Theme::asset()->container('footer')->scripts();
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

Theme have magic method to set or append anything.

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

Theme::place('foo');
~~~

> Theme::place('content') will render you sub-view only.

### Configuration

After your published config file you will see the config at "app/config/packages/teepluss/theme/config.php"

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

> The configuration file contents all of events that you can add css/js bootstrap for any themes.

## Widgets Design Structure

Theme have many useful features the one call "widget" that can be anything.

### Creating a first widget class

When you finished install package, you need to create a folder in your application that automatically loaded.

~~~
app/widgets
~~~

Now let create a first widget class name "WidgetIntro"

~~~
app/widgets/WidgetIntro.php
~~~

~~~php

use Teepluss\Theme\Widget;

class WidgetIntro extends widget {

    /**
     * Widget template
     *
     * This "$template" will look up the path
     * public/themes/[theme]/widgets/intro.php or
     * public/themes/[theme]/widgets/intro.blade.php.
     *
     * @type string
     */
    public $template = 'intro';

    /**
     * Widget arrtibutes
     *
     * @var array
     */
    public $attributes = array(
        'userId'  => null,
        'title'   => '',
        'body'    => ''
    );

    /**
     * Widget initialize
     *
     * When widget called init will the first method to run.
     *
     * @return void
     */
    public function init()
    {
        $this->attributes['userId'] = Auth::user()->id;
    }

    /**
     * Run a widget
     *
     * Pass attributes to a widget's view ($template).
     *
     * @return mixed
     */
    public function run()
    {
        // Get all attributes.
        $attributes = $this->getAttributes();

        // Get single attribute.
        $userId = $this->getAttribute('userId', 11);

        // Data to passing view.
        $data = array_merge($attributes, array('user' => User::find($userId));

        return $data;
    }

}
~~~

### Creating widget view

Every widgets need a will to support, so let's create a file like below:

~~~
public/themes/[theme]/widgets/intro.blade.php
~~~

~~~html
<h1>User Id: <?php echo $user->id; ?></h1>
<p>Name: <?php echo $user->name; ?></p>
~~~

### Calling your widget in layout or view

~~~php
echo Theme::widget('WidgetIntro', array('userId' => 9999, 'title' => 'Demo Widget'))->render();
~~~

## Support or Contact

If you have some problem, Contact teepluss@gmail.com