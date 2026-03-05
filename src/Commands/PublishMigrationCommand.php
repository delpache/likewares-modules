<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Likewares\Module\Migrations\Migrator;
use Likewares\Module\Publishing\MigrationPublisher;
use Symfony\Component\Console\Input\InputArgument;

class PublishMigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:publish-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Publier les migrations d'un module dans l'application";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($alias = $this->argument('alias')) {
            $module = $this->laravel['module']->findOrFail($alias);

            $this->publish($module);

            return;
        }

        foreach ($this->laravel['module']->allEnabled() as $module) {
            $this->publish($module);
        }
    }

    /**
     * Publish migration for the specified module.
     *
     * @param \Likewares\Module\Module $module
     */
    public function publish($module)
    {
        with(new MigrationPublisher(new Migrator($module, $this->getLaravel())))
            ->setRepository($this->laravel['module'])
            ->setConsole($this)
            ->publish();
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
}
