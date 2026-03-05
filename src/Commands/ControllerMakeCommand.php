<?php

namespace Likewares\Module\Commands;

use Illuminate\Support\Str;
use Likewares\Module\Support\Config\GenerateConfigReader;
use Likewares\Module\Support\Stub;
use Likewares\Module\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'controller';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer un nouveau contrôleur pour le module spécifié.';

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {
        $path = module()->getModulePath($this->getModuleAlias());

        $controllerPath = GenerateConfigReader::read('controller');

        return $path . $controllerPath->getPath() . '/' . $this->getControllerName() . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->getModule();

        return (new Stub($this->getStubName(), [
            'ALIAS'             => $module->getAlias(),
            'MODULENAME'        => $module->getStudlyName(),
            'CONTROLLERNAME'    => $this->getControllerName(),
            'NAMESPACE'         => $module->getStudlyName(),
            'CLASS_NAMESPACE'   => $this->getClassNamespace($module),
            'CLASS'             => $this->getControllerNameWithoutNamespace(),
            'MODULE'            => $this->getModuleName(),
            'NAME'              => $this->getModuleName(),
            'STUDLY_NAME'       => $module->getStudlyName(),
            'MODULE_NAMESPACE'  => $this->laravel['module']->config('namespace'),
        ]))->render();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['controller', InputArgument::REQUIRED, 'Nom du controller.'],
            ['alias', InputArgument::OPTIONAL, 'Alias du module qui sera utilisé.', null],
        ];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Générer un controller simple', null],
            ['api', null, InputOption::VALUE_NONE, 'Exclure les méthodes de création et de modification du controller.'],
        ];
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->argument('controller'));

        return $controller;
    }

    /**
     * @return array|string
     */
    protected function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    public function getDefaultNamespace() : string
    {
        return $this->laravel['module']->config('paths.generator.controller.path', 'Http/Controllers');
    }

    /**
     * Get the stub file name based on the options
     * @return string
     */
    protected function getStubName()
    {
        if ($this->option('plain') === true) {
            $stub = '/controller-plain.stub';
        } elseif ($this->option('api') === true) {
            $stub = '/controller-api.stub';
        } else {
            $stub = '/controller.stub';
        }

        return $stub;
    }
}
