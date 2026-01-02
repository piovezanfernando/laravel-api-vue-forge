<?php

use PiovezanFernando\LaravelApiVueForge\Commands\RollbackGeneratorCommand;
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
use Mockery as m;

use function Pest\Laravel\artisan;

afterEach(function () {
    m::close();
});

it('fails with invalid rollback type', function () {
    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'random'])
        ->assertExitCode(1);
});

function mockShouldHaveCalledRollbackGenerator(array $shouldHaveCalledGenerators): array
{
    $mockedObjects = [];

    foreach ($shouldHaveCalledGenerators as $generator) {
        $mock = m::mock($generator);

        $mock->shouldReceive('rollback')
            ->once()
            ->andReturn(true);

        app()->singleton($generator, function () use ($mock) {
            return $mock;
        });

        $mockedObjects[] = $mock;
    }

    return $mockedObjects;
}

function mockShouldNotHaveCalledRollbackGenerators(array $shouldNotHaveCalledGenerator): array
{
    $mockedObjects = [];

    foreach ($shouldNotHaveCalledGenerator as $generator) {
        $mock = m::mock($generator);

        $mock->shouldNotReceive('rollback');

        app()->singleton($generator, function () use ($mock) {
            return $mock;
        });

        $mockedObjects[] = $mock;
    }

    return $mockedObjects;
}



it('validates that all files are rolled back for api', function () {
    $shouldHaveCalledGenerators = [
        MigrationGenerator::class,
        ModelGenerator::class,
        APIRequestGenerator::class,
        APIControllerGenerator::class,
        APIRoutesGenerator::class,
        FactoryGenerator::class,
        SeederGenerator::class,
    ];

    mockShouldHaveCalledRollbackGenerator($shouldHaveCalledGenerators);

    $shouldNotHaveCalledGenerator = [
        RepositoryGenerator::class,
    ];

    mockShouldNotHaveCalledRollbackGenerators($shouldNotHaveCalledGenerator);

    config()->set('laravel_api_vue_forge.options.repository_pattern', false);
    config()->set('laravel_api_vue_forge.options.factory', true);
    config()->set('laravel_api_vue_forge.options.seeder', true);

    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'api']);
});


