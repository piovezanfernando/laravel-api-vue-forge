<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands\API;

use PiovezanFernando\LaravelApiVueForge\Commands\BaseCommand;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRequestGenerator;

class APIRequestsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiforge:api:requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an api request command';

    public function handle()
    {
        parent::handle();

        /** @var APIRequestGenerator $controllerGenerator */
        $controllerGenerator = app(APIRequestGenerator::class);
        $controllerGenerator->generate();

        $this->performPostActions();
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
