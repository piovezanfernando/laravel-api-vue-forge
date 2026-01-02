<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands;

use PiovezanFernando\LaravelApiVueForge\Common\GeneratorConfig;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIControllerGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRequestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRoutesGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APITestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\FactoryGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\MigrationGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ModelGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryTestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\SeederGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ServiceGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ServiceTestGenerator;
use Symfony\Component\Console\Input\InputArgument;

class RollbackGeneratorCommand extends BaseCommand
{
    public GeneratorConfig $config;

    protected $name = 'apiforge:rollback';

    protected $description = 'Rollback a full CRUD API for given model';

    public function handle()
    {
        $this->config = app(GeneratorConfig::class);
        $this->config->setCommand($this);
        $this->config->init();

        $type = $this->argument('type');
        if (!in_array($type, ['api'])) {
            $this->error('Invalid rollback type. Only "api" is supported.');

            return 1;
        }

        $this->fireFileDeletingEvent($type);


        $migrationGenerator = app(MigrationGenerator::class);
        $migrationGenerator->rollback();

        $modelGenerator = app(ModelGenerator::class);
        $modelGenerator->rollback();

        if ($this->config->options->repositoryPattern) {
            $repositoryGenerator = app(RepositoryGenerator::class);
            $repositoryGenerator->rollback();
        }

        if (in_array($type, ['api'])) {
            $requestGenerator = app(APIRequestGenerator::class);
            $requestGenerator->rollback();

            $controllerGenerator = app(APIControllerGenerator::class);
            $controllerGenerator->rollback();

            $routesGenerator = app(APIRoutesGenerator::class);
            $routesGenerator->rollback();
        }



        if ($this->config->options->tests) {
            $repositoryTestGenerator = app(RepositoryTestGenerator::class);
            $repositoryTestGenerator->rollback();

            $apiTestGenerator = app(APITestGenerator::class);
            $apiTestGenerator->rollback();
        }

        if ($this->config->options->factory or $this->config->options->tests) {
            $factoryGenerator = app(FactoryGenerator::class);
            $factoryGenerator->rollback();
        }

        if ($this->config->options->seeder) {
            $seederGenerator = app(SeederGenerator::class);
            $seederGenerator->rollback();
        }

        if ($this->config->options->servicePattern) {
            $serviceGenerator = app(ServiceGenerator::class);
            $serviceGenerator->rollback();
        }

        if ($this->config->options->servicePattern && $this->config->options->tests) {
            $serviceTestGenerator = app(ServiceTestGenerator::class);
            $serviceTestGenerator->rollback();
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();

        $this->fireFileDeletedEvent($type);

        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
            ['type', InputArgument::REQUIRED, 'Rollback type: (api)'],
        ];
    }
}
