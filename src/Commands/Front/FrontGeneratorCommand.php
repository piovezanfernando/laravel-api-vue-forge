<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands\Front;

use PiovezanFernando\LaravelApiVueForge\Commands\BaseCommand;

class FrontGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiforge:front-quasar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD Frontend for given model';

    public function handle()
    {
        parent::handle();
        $this->fireFileCreatingEvent('front');

        $this->generateFrontItems();

        $this->performPostActionsWithMigration();
        $this->fireFileCreatedEvent('front');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}