<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands\API;

use PiovezanFernando\LaravelApiVueForge\Commands\BaseCommand;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APITestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryTestGenerator;

class TestsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiforge:api:tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tests command';

    public function handle()
    {
        parent::handle();

        /** @var RepositoryTestGenerator $repositoryTestGenerator */
//        $repositoryTestGenerator = app(RepositoryTestGenerator::class);
//        $repositoryTestGenerator->generate();

        /** @var APITestGenerator $apiTestGenerator */
        $apiTestGenerator = app(APITestGenerator::class);
        $apiTestGenerator->generate();

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
