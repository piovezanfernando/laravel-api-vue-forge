<?php

namespace PiovezanFernando\LaravelApiVueForge\Common;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PiovezanFernando\LaravelApiVueForge\DTOs\GeneratorNamespaces;
use PiovezanFernando\LaravelApiVueForge\DTOs\GeneratorOptions;
use PiovezanFernando\LaravelApiVueForge\DTOs\GeneratorPaths;
use PiovezanFernando\LaravelApiVueForge\DTOs\GeneratorPrefixes;
use PiovezanFernando\LaravelApiVueForge\DTOs\ModelNames;

class GeneratorConfig
{
    public GeneratorNamespaces $namespaces;
    public GeneratorPaths $paths;
    public ModelNames $modelNames;
    public GeneratorPrefixes $prefixes;
    public GeneratorOptions $options;
    public Command $command;

    /** @var GeneratorField[] */
    public array $fields = [];

    /** @var GeneratorFieldRelation[] */
    public array $relations = [];

    protected static $dynamicVars = [];

    public $tableName;
    public string $tableType;
    public string $apiPrefix;
    public $primaryName;
    public $connection;

    public function init()
    {
        $this->loadModelNames();
        $this->loadPrefixes();
        $this->loadPaths();
        $this->tableType = config('laravel_api_vue_forge.tables', 'blade');
        $this->apiPrefix = config('laravel_api_vue_forge.api_prefix', 'api');
        $this->loadNamespaces();
        $this->prepareTable();
        $this->prepareOptions();
    }

    public static function addDynamicVariable(string $name, $value)
    {
        self::$dynamicVars[$name] = $value;
    }

    public static function addDynamicVariables(array $vars)
    {
        foreach ($vars as $key => $value) {
            self::addDynamicVariable($key, $value);
        }
    }

    public function getDynamicVariable(string $name)
    {
        return self::$dynamicVars[$name];
    }

    public function setCommand(Command &$command)
    {
        $this->command = &$command;
    }

    public function loadModelNames()
    {
        $modelNames = new ModelNames();
        $modelNames->name = $this->command->argument('model');

        if ($this->getOption('plural')) {
            $modelNames->plural = $this->getOption('plural');
        } else {
            $modelNames->plural = Str::plural($modelNames->name);
        }

        $modelNames->camel = Str::camel($modelNames->name);
        $modelNames->camelPlural = Str::camel($modelNames->plural);
        $modelNames->snake = Str::snake($modelNames->name);
        $modelNames->snakePlural = Str::snake($modelNames->plural);
        $modelNames->dashed = Str::kebab($modelNames->name);
        $modelNames->dashedPlural = Str::kebab($modelNames->plural);
        $modelNames->human = Str::title(str_replace('_', ' ', $modelNames->snake));
        $modelNames->humanPlural = Str::title(str_replace('_', ' ', $modelNames->snakePlural));

        $this->modelNames = $modelNames;
    }

    public function loadPrefixes()
    {
        $prefixes = new GeneratorPrefixes();

        $prefixes->route = config('laravel_api_vue_forge.prefixes.route', '');
        $prefixes->namespace = config('laravel_api_vue_forge.prefixes.namespace', '');
        $prefixes->view = config('laravel_api_vue_forge.prefixes.view', '');

        if ($this->getOption('prefix')) {
            $multiplePrefixes = explode('/', $this->getOption('prefix'));

            $prefixes->mergeRoutePrefix($multiplePrefixes);
            $prefixes->mergeNamespacePrefix($multiplePrefixes);
            $prefixes->mergeViewPrefix($multiplePrefixes);
        }

        $this->prefixes = $prefixes;
    }

    public function loadPaths()
    {
        $paths = new GeneratorPaths();

        $namespacePrefix = $this->prefixes->namespace;
        $viewPrefix = $this->prefixes->view;

        if (!empty($namespacePrefix)) {
            $namespacePrefix .= '/';
        }

        if (!empty($viewPrefix)) {
            $viewPrefix .= '/';
        }

        $paths->repository = config(
            'laravel_api_vue_forge.path.repository',
            app_path('Repositories/')
        ).$namespacePrefix;

        $paths->service = config('laravel_api_vue_forge.path.service', app_path('Services/')).$namespacePrefix;

        $paths->model = config('laravel_api_vue_forge.path.model', app_path('Models/')).$namespacePrefix;

        $paths->dataTables = config(
            'laravel_api_vue_forge.path.datatables',
            app_path('DataTables/')
        ).$namespacePrefix;

        $paths->livewireTables = config(
            'laravel_api_vue_forge.path.livewire_tables',
            app_path('Http/Livewire/')
        );

        $paths->apiController = config(
            'laravel_api_vue_forge.path.api_controller',
            app_path('Http/Controllers/API/')
        ).$namespacePrefix;

        $paths->apiResource = config(
            'laravel_api_vue_forge.path.api_resource',
            app_path('Http/Resources/')
        ).$namespacePrefix;

        $paths->apiRequest = config(
            'laravel_api_vue_forge.path.api_request',
            app_path('Http/Requests/API/')
        ).$namespacePrefix;

        $paths->apiRoutes = config(
            'laravel_api_vue_forge.path.api_routes',
            base_path('routes/api.php')
        );

        $paths->apiTests = config('laravel_api_vue_forge.path.api_test', base_path('tests/APIs/'));

        $paths->controller = config(
            'laravel_api_vue_forge.path.controller',
            app_path('Http/Controllers/')
        ).$namespacePrefix;

        $paths->request = config('laravel_api_vue_forge.path.request', app_path('Http/Requests/')).$namespacePrefix;

        $paths->routes = config('laravel_api_vue_forge.path.routes', base_path('routes/web.php'));
        $paths->factory = config('laravel_api_vue_forge.path.factory', database_path('factories/'));

        $paths->views = config(
            'laravel_api_vue_forge.path.views',
            resource_path('views/')
        ).$viewPrefix.$this->modelNames->snakePlural.'/';

        $paths->seeder = config('laravel_api_vue_forge.path.seeder', database_path('seeders/'));
        $paths->databaseSeeder = config('laravel_api_vue_forge.path.database_seeder', database_path('seeders/DatabaseSeeder.php'));
        $paths->viewProvider = config(
            'laravel_api_vue_forge.path.view_provider',
            app_path('Providers/ViewServiceProvider.php')
        );

        $paths->frontend = config('laravel_api_vue_forge.path.frontend', base_path('front/'));

        $this->paths = $paths;
    }

    public function loadNamespaces()
    {
        $prefix = $this->prefixes->namespace;

        if (!empty($prefix)) {
            $prefix = '\\'.$prefix;
        }

        $namespaces = new GeneratorNamespaces();

        $namespaces->app = app()->getNamespace();
        $namespaces->app = substr($namespaces->app, 0, strlen($namespaces->app) - 1);
        $namespaces->repository = config('laravel_api_vue_forge.namespace.repository', 'App\Repositories').$prefix;
        $namespaces->services = config('laravel_api_vue_forge.namespace.services', 'App\Services').$prefix;
        $namespaces->model = config('laravel_api_vue_forge.namespace.model', 'App\Models').$prefix;
        $namespaces->seeder = config('laravel_api_vue_forge.namespace.seeder', 'Database\Seeders').$prefix;
        $namespaces->factory = config('laravel_api_vue_forge.namespace.factory', 'Database\Factories').$prefix;
        $namespaces->dataTables = config('laravel_api_vue_forge.namespace.datatables', 'App\DataTables').$prefix;
        $namespaces->livewireTables = config('laravel_api_vue_forge.namespace.livewire_tables', 'App\Http\Livewire');
        $namespaces->modelExtend = config(
            'laravel_api_vue_forge.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $namespaces->apiController = config(
            'laravel_api_vue_forge.namespace.api_controller',
            'App\Http\Controllers\API'
        ).$prefix;
        $namespaces->apiResource = config(
            'laravel_api_vue_forge.namespace.api_resource',
            'App\Http\Resources'
        ).$prefix;

        $namespaces->apiRequest = config(
            'laravel_api_vue_forge.namespace.api_request',
            'App\Http\Requests\API'
        ).$prefix;

        $namespaces->request = config(
            'laravel_api_vue_forge.namespace.request',
            'App\Http\Requests'
        ).$prefix;
        $namespaces->requestBase = config('laravel_api_vue_forge.namespace.request', 'App\Http\Requests');
        $namespaces->baseController = config('laravel_api_vue_forge.namespace.controller', 'App\Http\Controllers');
        $namespaces->controller = config(
            'laravel_api_vue_forge.namespace.controller',
            'App\Http\Controllers'
        ).$prefix;

        $namespaces->apiTests = config('laravel_api_vue_forge.namespace.api_test', 'Tests\APIs');
        $namespaces->repositoryTests = config('laravel_api_vue_forge.namespace.repository_test', 'Tests\Repositories');
        $namespaces->serviceTests = config('laravel_api_vue_forge.namespace.service_test', 'Tests\Services');
        $namespaces->tests = config('laravel_api_vue_forge.namespace.tests', 'Tests');

        $this->namespaces = $namespaces;
    }

    public function prepareTable()
    {
        if ($this->getOption('table')) {
            $this->tableName = $this->getOption('table');
        } else {
            $this->tableName = $this->modelNames->snakePlural;
        }

        if ($this->getOption('primary')) {
            $this->primaryName = $this->getOption('primary');
        } else {
            $this->primaryName = 'id';
        }

        if ($this->getOption('connection')) {
            $this->connection = $this->getOption('connection');
        }
    }

    public function prepareOptions()
    {
        $options = new GeneratorOptions();

        $options->softDelete = config('laravel_api_vue_forge.options.soft_delete', false);
        $options->saveSchemaFile = config('laravel_api_vue_forge.options.save_schema_file', true);
        $options->localized = config('laravel_api_vue_forge.options.localized', false);
        $options->repositoryPattern = config('laravel_api_vue_forge.options.repository_pattern', true);
        $options->resources = config('laravel_api_vue_forge.options.resources', false);
        $options->factory = config('laravel_api_vue_forge.options.factory', false);
        $options->seeder = config('laravel_api_vue_forge.options.seeder', false);
        $options->swagger = config('laravel_api_vue_forge.options.swagger', false);
        $options->tests = config('laravel_api_vue_forge.options.tests', false);
        $options->excludedFields = config('laravel_api_vue_forge.options.excluded_fields', ['id']);
        $options->excludedFillable = config('laravel_api_vue_forge.options.excluded_fillable', ['id']);
        $options->servicePattern = config('laravel_api_vue_forge.options.service_pattern', false);
        $options->uniqueRequest = config('laravel_api_vue_forge.options.unique_request', false);

        $this->options = $options;
    }

    public function overrideOptionsFromJsonFile($jsonData)
    {
//        $options = self::$availableOptions;
//
//        foreach ($options as $option) {
//            if (isset($jsonData['options'][$option])) {
//                $this->setOption($option, $jsonData['options'][$option]);
//            }
//        }
//
//        // prepare prefixes than reload namespaces, paths and dynamic variables
//        if (!empty($this->getOption('prefix'))) {
//            $this->preparePrefixes();
//            $this->loadPaths();
//            $this->loadNamespaces();
//            $this->loadDynamicVariables();
//        }
//
//        $addOns = ['swagger', 'tests', 'datatables'];
//
//        foreach ($addOns as $addOn) {
//            if (isset($jsonData['addOns'][$addOn])) {
//                $this->addOns[$addOn] = $jsonData['addOns'][$addOn];
//            }
//        }
    }

    public function getOption($option)
    {
        return $this->command->option($option);
    }

    public function commandError($error)
    {
        $this->command->error($error);
    }

    public function commandComment($message)
    {
        $this->command->comment($message);
    }

    public function commandWarn($warning)
    {
        $this->command->warn($warning);
    }

    public function commandInfo($message)
    {
        $this->command->info($message);
    }
}
