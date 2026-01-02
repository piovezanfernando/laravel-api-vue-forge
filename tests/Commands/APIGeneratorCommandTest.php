<?php

use PiovezanFernando\LaravelApiVueForge\Commands\API\APIGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Facades\FileUtils;
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
use Illuminate\Support\Facades\Schema;
use Mockery as m;

use function Pest\Laravel\artisan;

afterEach(function () {
    m::close();
});

it('generates all files for api from existing table', function () {
    FileUtils::fake();

    $shouldHaveCalledGenerators = [
        ModelGenerator::class,
        APIRequestGenerator::class,
        APIControllerGenerator::class,
        APIRoutesGenerator::class,
    ];

    mockShouldHaveCalledGenerateMethod($shouldHaveCalledGenerators);

    $shouldNotHaveCalledGenerator = [
        MigrationGenerator::class,
        RepositoryGenerator::class,
        RepositoryTestGenerator::class,
        APITestGenerator::class,
        FactoryGenerator::class,
    ];

    mockShouldNotHaveCalledGenerateMethod($shouldNotHaveCalledGenerator);

    config()->set('laravel_api_vue_forge.options.repository_pattern', false);
    config()->set('laravel_api_vue_forge.options.tests', false);
    config()->set('laravel_api_vue_forge.options.factory', false);

    Schema::shouldReceive('getColumns')
        ->with('posts')
        ->andReturn([
            ['name' => 'id', 'type_name' => 'integer', 'type' => 'integer', 'nullable' => false, 'auto_increment' => true],
            ['name' => 'title', 'type_name' => 'string', 'type' => 'string', 'nullable' => false, 'comment' => ''],
            ['name' => 'created_at', 'type_name' => 'datetime', 'type' => 'datetime', 'nullable' => true],
            ['name' => 'updated_at', 'type_name' => 'datetime', 'type' => 'datetime', 'nullable' => true],
        ]);

    Schema::shouldReceive('getIndexes')
        ->with('posts')
        ->andReturn([
            ['name' => 'primary', 'primary' => true, 'columns' => ['id']],
        ]);

    Schema::shouldReceive('getTables')
        ->andReturn([
            ['name' => 'posts'],
        ]);

    Schema::shouldReceive('getForeignKeys')
        ->with('posts')
        ->andReturn([]);

    artisan(APIGeneratorCommand::class, ['model' => 'Post', '--fromTable' => true, '--table' => 'posts'])
        ->assertSuccessful();
});
