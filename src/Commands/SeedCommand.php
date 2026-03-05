<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Str;
use Likewares\Module\Contracts\IRepositoryInterface;
use Likewares\Module\Module;
use Likewares\Module\Support\Config\GenerateConfigReader;
use Likewares\Module\Traits\ModuleCommandTrait;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SeedCommand extends Command
{
    use ModuleCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécuter le seeder de base de données à partir du module spécifié ou de tous les modules.';

    /**
     * Execute the console command.
     * @throws FatalThrowableError
     */
    public function handle()
    {
        try {
            if ($alias = $this->argument('alias')) {
                $this->moduleSeed($this->getModuleByAlias($alias));
            } else {
                $modules = $this->getModuleRepository()->getOrdered();
                array_walk($modules, [$this, 'moduleSeed']);
                $this->info('All modules seeded.');
            }
        } catch (\Throwable $e) {
            $this->reportException($e);

            $this->renderException($this->getOutput(), $e);

            return 1;
        }
    }

    /**
     * @throws RuntimeException
     * @return IRepositoryInterface
     */
    public function getModuleRepository(): IRepositoryInterface
    {
        $module = $this->laravel['module'];

        if (!$module instanceof IRepositoryInterface) {
            throw new RuntimeException('Module repository introuvable!');
        }

        return $module;
    }

    /**
     * @param $alias
     *
     * @throws RuntimeException
     *
     * @return Module
     */
    public function getModuleByAlias($alias)
    {
        $module = $this->getModuleRepository();

        if ($module->has($alias) === false) {
            throw new RuntimeException("Module [$alias] n'existe pas.");
        }

        return $module->find($alias);
    }

    /**
     * @param Module $module
     *
     * @return void
     */
    public function moduleSeed(Module $module)
    {
        $seeders = [];
        $alias = $module->getAlias();

        $config = $module->get('migration');

        if (is_array($config) && array_key_exists('seeds', $config)) {
            foreach ((array) $config['seeds'] as $class) {
                if (class_exists($class)) {
                    $seeders[] = $class;
                }
            }
        } else {
            $class = $this->getSeederName($alias);

            //legacy support
            if (class_exists($class)) {
                $seeders[] = $class;
            } else {
                //look at other namespaces
                $classes = $this->getSeederNames($alias);

                foreach ($classes as $class) {
                    if (class_exists($class)) {
                        $seeders[] = $class;
                    }
                }
            }
        }

        if (count($seeders) > 0) {
            array_walk($seeders, [$this, 'dbSeed']);
            $this->info("Module [$alias] seeded.");
        }
    }

    /**
     * Seed the specified module.
     *
     * @param string $className
     */
    protected function dbSeed($className)
    {
        if ($option = $this->option('class')) {
            $params['--class'] = Str::finish(substr($className, 0, strrpos($className, '\\')), '\\') . $option;
        } else {
            $params = ['--class' => $className];
        }

        if ($option = $this->option('database')) {
            $params['--database'] = $option;
        }

        if ($option = $this->option('force')) {
            $params['--force'] = $option;
        }

        $this->call('db:seed', $params);
    }

    /**
     * Get master database seeder name for the specified module.
     *
     * @param string $alias
     *
     * @return string
     */
    public function getSeederName($alias)
    {
        $name = Str::studly($alias);

        $namespace = $this->laravel['module']->config('namespace');
        $seederPath = GenerateConfigReader::read('seeder');
        $seederPath = str_replace('/', '\\', $seederPath->getPath());

        return $namespace . '\\' . $name . '\\' . $seederPath . '\\' . $name . 'DatabaseSeeder';
    }

    /**
     * Get master database seeder name for the specified module under a different namespace than Modules.
     *
     * @param string $alias
     *
     * @return array $foundModules array containing namespace paths
     */
    public function getSeederNames($alias)
    {
        $name = Str::studly($alias);

        $seederPath = GenerateConfigReader::read('seeder');
        $seederPath = str_replace('/', '\\', $seederPath->getPath());

        $foundModules = [];
        foreach ($this->laravel['module']->config('scan.paths') as $path) {
            $namespace = array_slice(explode('/', $path), -1)[0];
            $foundModules[] = $namespace . '\\' . $name . '\\' . $seederPath . '\\' . $name . 'DatabaseSeeder';
        }

        return $foundModules;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderException($output, \Throwable $e)
    {
        $this->laravel[ExceptionHandler::class]->renderForConsole($output, $e);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function reportException(\Throwable $e)
    {
        $this->laravel[ExceptionHandler::class]->report($e);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['alias', InputArgument::OPTIONAL, 'Alias du module qui sera utilisé.', null],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['class', null, InputOption::VALUE_OPTIONAL, 'Nom de la classe seeder racine.'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'Connexion à la base de données à seeder.'],
            ['force', null, InputOption::VALUE_NONE, 'Forcer l\'exécution de l\'opération en production.'],
        ];
    }
}
