<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Likewares\Module\Migrations\Migrator;
use Likewares\Module\Module;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateStatusCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Statut de toutes les migrations de modules';

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

            return $this->migrateStatus($module);
        }

        foreach ($this->module->getOrdered($this->option('direction')) as $module) {
            $this->line('Exécution pour le module: <info>' . $module->getAlias() . '</info>');
            $this->migrateStatus($module);
        }
    }

    /**
     * Run the migration from the specified module.
     *
     * @param Module $module
     */
    protected function migrateStatus(Module $module)
    {
        $path = str_replace(base_path(), '', (new Migrator($module, $this->getLaravel()))->getPath());

        $this->call('migrate:status', [
            '--path' => $path,
            '--database' => $this->option('database'),
        ]);
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
        ];
    }
}
