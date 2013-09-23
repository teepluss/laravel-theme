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
     * @return void
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
        if (preg_match('/^[A-Z]/', $this->argument('name')))
        {
            return $this->error('First character of widget name must be lowercase.');
        }

        // Widget class name is camel case.
        $widgetClassName = 'Widget'.$this->getWidgetName();

        // Widget class file is camel with php extension.
        $widgetClassFile = $widgetClassName.'.php';

        // Widget template is lower.
        $widgetClassTpl = $this->getWidgetName();
        $widgetClassTpl = lcfirst($widgetClassTpl);

        // Get class template.
        $widgetClassTemplate = $this->getTemplate('widgetClass');

        // Directories.
        $container = $this->config->get('theme::containerDir');

        // Prepare class template.
        $widgetClassTemplate = preg_replace(
            array('|\{widgetClass\}|', '|\{widgetTemplate\}|'),
            array($widgetClassName, $widgetClassTpl),
            $widgetClassTemplate
        );

        // Create widget directory.
        if ( ! $this->files->isDirectory(app_path().'/widgets'))
        {
            $this->files->makeDirectory(app_path().'/widgets', 0777, true);
        }

        // Widget class already exists.
        if ($this->files->exists(app_path().'/widgets/'.$widgetClassFile))
        {
            return $this->error('Widget "'.$this->getWidgetName().'" is already exists.');
        }

        // Create class file.
        $this->files->put(app_path().'/widgets/'.$widgetClassFile, $widgetClassTemplate);

        // What is type you want?
        $type = $this->option('type');

        if ( ! in_array($type, array('php', 'blade', 'twig')))
        {
            // Blade or html.
            $question = $this->ask('What type of template? (php, blade, twig)');

            $type = in_array($question, array('php', 'blade', 'twig')) ? $question : 'php';
        }

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
        $rootPath = $this->option('path');

        return $rootPath.'/'.strtolower($this->getTheme()).'/' . $path;
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
            array('theme', InputArgument::REQUIRED, 'Theme name to generate widget view file.')
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $path = public_path().'/'.$this->config->get('theme::themeDir');

        return array(
            array('path', null, InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path),
            array('type', null, InputOption::VALUE_OPTIONAL, 'php, blade or twig.', null)
        );
    }

}