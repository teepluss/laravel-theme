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

First time you have to create theme "default" structure like below:

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

}
~~~

