<?php namespace Teepluss\Theme\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem as File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WidgetGeneratorCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate widget structure';

    /**
     * Widget view template global.
     *
     * @var boolean
     */
    protected $global = false;

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
     * @return \Teepluss\Theme\Commands\WidgetGeneratorCommand
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
        // Widget class name is camel case.
        $widgetClassName = ucfirst($this->getWidgetName());

        // Widget class file is camel with php extension.
        $widgetClassFile = $widgetClassName.'.php';

        // CamelCase for template.
        $widgetClassTpl = lcfirst($this->getWidgetName());

        // Get class template.
        $widgetClassTemplate = $this->getTemplate('widgetClass');

        // Directories.
        $container = $this->config->get('theme.containerDir');

        // Default create not on a global.
        $watch = 'false';

        // If not specific a theme, not a global also return an error.
        if ($this->option('global') === false and ! $this->argument('theme'))
        {
            return $this->error('Please specific a theme name or use option -g to create as a global widget.');
        }

        // Create as a global use -g.
        if ($this->option('global') === true)
        {
            $watch = 'true';
        }

        // What is type you want?
        $type = $this->option('type');

        if ( ! in_array($type, array('php', 'blade', 'twig')))
        {
            // Blade or html.
            $question = $this->ask('What type of widget template? [php|blade|twig]');

            $type = in_array($question, array('php', 'blade', 'twig')) ? $question : 'php';
        }

        $widgetNamespace = $this->config->get('theme.namespaces.widget');

        // Prepare class template.
        $widgetClassTemplate = preg_replace(
            array('|\{widgetNamespace\}|', '|\{widgetClass\}|', '|\{widgetTemplate\}|', '|\{watch\}|'),
            array($widgetNamespace, $widgetClassName, $widgetClassTpl, $watch),
            $widgetClassTemplate
        );

        // Create widget directory.
        if ( ! $this->files->isDirectory(app_path().'/Widgets'))
        {
            $this->files->makeDirectory(app_path().'/Widgets', 0777, true);
        }

        // Widget class already exists.
        if ($this->files->exists(app_path().'/Widgets/'.$widgetClassFile))
        {
            return $this->error('Widget "'.$this->getWidgetName().'" is already exists.');
        }

        // Create class file.
        $this->files->put(app_path().'/Widgets/'.$widgetClassFile, $widgetClassTemplate);

        // Make file example.
        switch ($type)
        {
            case 'blade' :
                $this->makeFile($container['widget'].'/'.$widgetClassTpl.'.blade.php', $this->getTemplate('widget.blade'));
                break;
            case 'twig' :
                $this->makeFile($container['widget'].'/'.$widgetClassTpl.'.twig.php', $this->getTemplate('widget.twig'));
                break;
            default :
                $this->makeFile($container['widget'].'/'.$widgetClassTpl.'.php', $this->getTemplate('widget'));
                break;
        }

        $this->info('Widget "'.$this->getWidgetName().'" has been created.');
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
        $dirname = dirname($this->getPath($file));

        // Checking directory.
        if ( ! $this->argument('theme') and ! $this->files->isDirectory($dirname))
        {
            $this->files->makeDirectory($dirname, 0777, true);
        }

        if ( ! $this->files->exists($this->getPath($file)))
        {
            $this->files->put($this->getPath($file), $template);
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
        // If not specific theme name, so widget will creating as global.
        if ( ! $this->argument('theme'))
        {
            return base_path('resources/views/'.$path);
        }

        $rootPath = $this->option('path');

        return $rootPath.'/'.$this->getTheme().'/' . $path;
    }

    /**
     * Get the widget name.
     *
     * @return string
     */
    protected function getWidgetName()
    {
        // The first character must be lower.
        return ucfirst($this->argument('name'));
    }

    /**
     * Get the theme name.
     *
     * @return string
     */
    protected function getTheme()
    {
        return strtolower($this->argument('theme'));
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
            array('name', InputArgument::REQUIRED, 'Name of the widget to generate.'),
            array('theme', InputArgument::OPTIONAL, 'Theme name to generate widget view file.')
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $path = public_path($this->config->get('theme.themeDir'));

        return array(
            array('path', 'p', InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path),
            array('type', 't', InputOption::VALUE_OPTIONAL, 'Widget view type [php|blade|twig].', null),
            array('global', 'g', InputOption::VALUE_NONE, 'Create global widget.', null)
        );
    }

}