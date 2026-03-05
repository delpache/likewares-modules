<?php

namespace Likewares\Module\Commands;

use Illuminate\Support\Str;
use Likewares\Module\Support\Config\GenerateConfigReader;
use Likewares\Module\Support\Stub;
use Likewares\Module\Traits\CanClearModulesCache;
use Likewares\Module\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SeedMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait, CanClearModulesCache;

    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer un nouveau seeder pour le module spécifié.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Nom du seeder sera créé.'],
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
            [
                'master',
                null,
                InputOption::VALUE_NONE,
                'Indique que le seeder créé est un seeder de la base de données principale.',
            ],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $module = $this->getModule();

        return (new Stub('/seeder.stub', [
            'NAME' => $this->getSeederName(),
            'ALIAS' => $module->getAlias(),
            'MODULE' => $this->getModuleName(),
            'NAMESPACE' => $this->getClassNamespace($module),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $this->clearCache();

        $path = module()->getModulePath($this->getModuleAlias());

        $seederPath = GenerateConfigReader::read('seeder');

        return $path . $seederPath->getPath() . '/' . $this->getSeederName() . '.php';
    }

    /**
     * Get seeder name.
     *
     * @return string
     */
    private function getSeederName()
    {
        $end = $this->option('master') ? 'DatabaseSeeder' : '';

        return Str::studly($this->argument('name')) . $end;
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace() : string
    {
        return module()->config('paths.generator.seeder.path', 'Database/Seeds');
    }
}
