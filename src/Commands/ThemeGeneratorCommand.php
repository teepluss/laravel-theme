<?php namespace Teepluss\Theme\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem as File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ThemeGeneratorCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate theme structure';

	/**
	 * Repository config.
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $files;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return \Teepluss\Theme\Commands\ThemeGeneratorCommand
     */
	public function __construct(Repository $config, File $files)
	{
		$this->config = $config;

		$this->files = $files;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		// The theme is already exists.
		if ($this->files->isDirectory($this->getPath(null)))
		{
			return $this->error('Theme "'.$this->getTheme().'" is already exists.');
		}

		$type = $this->option('type');

		if ( ! in_array($type, array('php', 'blade', 'twig')))
		{
			// Blade or html.
			$question = $this->ask('What type of template? [php|blade|twig]');

			$type = in_array($question, array('php', 'blade', 'twig')) ? $question : 'php';
		}

		// Directories.
		$container = $this->config->get('theme.containerDir');

		$this->makeDir($container['asset'].'/css');
		$this->makeDir($container['asset'].'/js');
		$this->makeDir($container['asset'].'/img');
		$this->makeDir($container['layout']);
		$this->makeDir($container['partial']);
		$this->makeDir($container['view']);
		$this->makeDir($container['widget']);

		// Default layout.
		$layout = $this->config->get('theme.layoutDefault');

		// Make file example.
		switch ($type)
		{
			case 'blade' :
				$this->makeFile('layouts/'.$layout.'.blade.php', $this->getTemplate('layout.blade'));
				$this->makeFile('partials/header.blade.php', $this->getTemplate('header'));
				$this->makeFile('partials/footer.blade.php', $this->getTemplate('footer'));
				break;

			case 'twig' :
				$this->makeFile('layouts/'.$layout.'.twig.php', $this->getTemplate('layout.twig'));
				$this->makeFile('partials/header.twig.php', $this->getTemplate('header'));
				$this->makeFile('partials/footer.twig.php', $this->getTemplate('footer'));
				break;

			default :
				$this->makeFile('layouts/'.$layout.'.php', $this->getTemplate('layout'));
				$this->makeFile('partials/header.php', $this->getTemplate('header'));
				$this->makeFile('partials/footer.php', $this->getTemplate('footer'));
				break;
		}

		// Generate inside config.
		$this->makeFile('config.php', $this->getTemplate('config'));

		$this->info('Theme "'.$this->getTheme().'" has been created.');
	}

	/**
	 * Make directory.
	 *
	 * @param  string $directory
	 * @return void
	 */
	protected function makeDir($directory)
	{
		if ( ! $this->files->isDirectory($this->getPath($directory)))
		{
			$this->files->makeDirectory($this->getPath($directory), 0777, true);
		}
	}

	/**
	 * Make file.
	 *
	 * @param  string $file
	 * @param  string $template
	 * @return void
	 */
	protected function makeFile($file, $template = null)
	{
		if ( ! $this->files->exists($this->getPath($file)))
		{
			$content = $this->getPath($file);

			$facade = $this->option('facade');
			if ( ! is_null($facade))
			{
				$template = preg_replace('/Theme(\.|::)/', $facade.'$1', $template);
			}

			$this->files->put($content, $template);
		}
	}

	/**
	 * Get root writable path.
	 *
	 * @param  string $path
	 * @return string
	 */
	protected function getPath($path)
	{
		$rootPath = $this->option('path');

		return $rootPath.'/'.strtolower($this->getTheme()).'/' . $path;
	}

	/**
	 * Get the theme name.
	 *
	 * @return string
	 */
	protected function getTheme()
	{
		return strtolower($this->argument('name'));
	}

	/**
	 * Get default template.
	 *
	 * @param  string $template
	 * @return string
	 */
	protected function getTemplate($template)
	{
		$path = realpath(__DIR__.'/../templates/'.$template.'.txt');

		return $this->files->get($path);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'Name of the theme to generate.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$path = public_path().'/'.$this->config->get('theme.themeDir');

		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path),
			array('type', null, InputOption::VALUE_OPTIONAL, 'Theme view type [php|blade|twig].', null),
			array('facade', null, InputOption::VALUE_OPTIONAL, 'Facade name.', null),
		);
	}

}