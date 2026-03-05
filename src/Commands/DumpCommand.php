<?php

namespace Likewares\Module\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DumpCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump-autoload du module spécifié ou de tous les modules.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Générer des modules autoload optimisés.');

        if ($module = $this->argument('alias')) {
            $this->dump($module);
        } else {
            foreach ($this->laravel['module']->all() as $module) {
                $this->dump($module->getStudlyName());
            }
        }
    }

    public function dump($module)
    {
        $module = $this->laravel['module']->findOrFail($module);

        $this->line("<comment>Running for module</comment>: {$module}");

        chdir($module->getPath());

        passthru('composer dump -o -n -q');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['alias', InputArgument::OPTIONAL, 'Module alias.', null],
        ];
    }
}
