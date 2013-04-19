<?php

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
		'widget'  => 'widgets'
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
			// 	$theme->setTitle('Asian theme is set');
			// }

		),

		// Listen on event set up layout.
		'onSetLayout' => array(

			'default' => function($theme)
			{
				$theme->setTitle('This is layout');
			},

			// 'summer' => function($theme)
			// {
			// 	$theme->setTitle('Layout summer is set')
			// }

		),

		// Listen on event set up theme and layout
		'onSetThemeWithLayout' => array(

			'defaultDefault' => function($theme)
			{
				$theme->setTitle('This is theme and layout');
			},

			// 'asianSummer' => function($theme)
			// {
			// 	$theme->setTitle('Theme asian and layout summer is set');
			// }

		)

	)

);