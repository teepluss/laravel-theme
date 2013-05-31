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

[![endorse](https://api.coderwall.com/teepluss/endorsecount.png)](https://coderwall.com/teepluss)