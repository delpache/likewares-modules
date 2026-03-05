<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Likewares\Module\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateRefreshCommand extends Command
{
    use ModuleCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback et migrer à nouveau les modules migrés.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('module:migrate-reset', [
            'alias' => $this->getModuleAlias(),
            '--database' => $this->option('database'),
            '--force' => $this->option('force'),
        ]);

        $this->call('module:migrate', [
            'alias' => $this->getModuleAlias(),
            '--database' => $this->option('database'),
            '--force' => $this->option('force'),
        ]);

        if ($this->option('seed')) {
            $this->call('module:seed', [
                'alias' => $this->getModuleAlias(),
            ]);
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
            ['database', null, InputOption::VALUE_OPTIONAL, 'Connexion à la base de données à utiliser.'],
            ['force', null, InputOption::VALUE_NONE, 'Forcer l\'exécution de l\'opération en production.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indique si la tâche de seeding doit être réexécutée.'],
        ];
    }
}
