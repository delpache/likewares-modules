<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Likewares\Module\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;

class UpdateCommand extends Command
{
    use ModuleCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mise à jour des dépendances pour le module spécifié ou pour tous les modules.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alias = $this->argument('alias');

        if ($alias) {
            $this->updateModule($alias);

            return;
        }

        /** @var \Likewares\Module\Module $module */
        foreach ($this->laravel['module']->getOrdered() as $module) {
            $this->updateModule($module->getAlias());
        }
    }

    protected function updateModule($alias)
    {
        $this->line('Running for module: <info>' . $alias . '</info>');

        $this->laravel['module']->update($alias);

        $this->info("Module [{$alias}] mis à jour avec succès.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['alias', InputArgument::OPTIONAL, 'Alias du module sera mis à jour.', null],
        ];
    }
}
