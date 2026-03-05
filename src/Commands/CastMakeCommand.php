<?php

namespace Likewares\Module\Commands;

use Likewares\Module\Support\Config\GenerateConfigReader;
use Likewares\Module\Support\Stub;
use Likewares\Module\Traits\ModuleCommandTrait;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class CastMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-cast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer une nouvelle classe de distribution Eloquent personnalisée pour le module spécifié';

    public function getTemplateContents()
    {
        $module = $this->getModule();

        return (new Stub('/cast.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClass(),
        ]))->render();
    }

    public function getDestinationFilePath()
    {
        $path = $this->laravel['module']->getModulePath($this->getModuleAlias());

        $castPath = GenerateConfigReader::read('cast');

        return $path . $castPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return Str::studly($this->argument('name'));
    }

    public function getDefaultNamespace(): string
    {
        return $this->laravel['module']->config('paths.generator.cast.path', 'Casts');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Nom du cast.'],
            ['alias', InputArgument::OPTIONAL, 'Alias du module qui sera utilisé.', null],
        ];
    }
}
