<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Likewares\Module\Migrations\Migrator;
use Likewares\Module\Module;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrer les migrations du module spécifié ou de tous les modules.';

    /**
     * @var \Likewares\Module\Contracts\RepositoryInterface
     */
    protected $module;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = $this->laravel['module'];

        $alias = $this->argument('alias');

        if ($alias) {
            $module = $this->module->findOrFail($alias);

            return $this->migrate($module);
        }

        foreach ($this->module->getOrdered($this->option('direction')) as $module) {
            $this->line('Running for module: <info>' . $module->getName() . '</info>');

            $this->migrate($module);
        }
    }

    /**
     * Run the migration from the specified module.
     *
     * @param Module $module
     */
    protected function migrate(Module $module)
    {
        $path = str_replace(base_path(), '', (new Migrator($module, $this->getLaravel()))->getPath());

        if ($this->option('subpath')) {
            $path = $path . "/" . $this->option("subpath");
        }

        $this->call('migrate', [
            '--path' => $path,
            '--database' => $this->option('database'),
            '--pretend' => $this->option('pretend'),
            '--force' => $this->option('force'),
        ]);

        if ($this->option('seed')) {
            $this->call('module:seed', ['alias' => $module->getAlias()]);
        }
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
            ['direction', 'd', InputOption::VALUE_OPTIONAL, 'Sens de l\'ordonnancement.', 'asc'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'Connexion à la base de données à utiliser.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Vider les requêtes SQL qui seraient exécutées.'],
            ['force', null, InputOption::VALUE_NONE, 'Forcer l\'exécution de l\'opération en production.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indique si la tâche de seeding doit être réexécutée.'],
            ['subpath', null, InputOption::VALUE_OPTIONAL, 'Indiquez un sous-chemin à partir duquel exécuter vos migrations'],
        ];
    }
}
