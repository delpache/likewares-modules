<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Likewares\Module\Migrations\Migrator;
use Likewares\Module\Traits\MigrationLoaderTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateResetCommand extends Command
{
    use MigrationLoaderTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialiser les migrations des modules.';

    /**
     * @var \Likewares\Module\Contracts\RepositoryInterface
     */
    protected $module;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->module = $this->laravel['module'];

        $alias = $this->argument('alias');

        if (!empty($alias)) {
            $this->reset($alias);

            return;
        }

        foreach ($this->module->getOrdered($this->option('direction')) as $module) {
            $this->line('Running for module: <info>' . $module->getAlias() . '</info>');

            $this->reset($module);
        }
    }

    /**
     * Rollback migration from the specified module.
     *
     * @param $module
     */
    public function reset($module)
    {
        if (is_string($module)) {
            $module = $this->module->findOrFail($module);
        }

        $migrator = new Migrator($module, $this->getLaravel());

        $database = $this->option('database');

        if (!empty($database)) {
            $migrator->setDatabase($database);
        }

        $migrated = $migrator->reset();

        if (count($migrated)) {
            foreach ($migrated as $migration) {
                $this->line("Rollback: <info>{$migration}</info>");
            }

            return;
        }

        $this->comment('Aucun rollback.');
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
            ['force', null, InputOption::VALUE_NONE, 'Forcer l\'exécution de l\'opération en production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Vider les requêtes SQL qui seraient exécutées.'],
        ];
    }
}
