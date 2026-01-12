<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorConfig;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorField;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorFieldRelation;
use PiovezanFernando\LaravelApiVueForge\Events\GeneratorFileCreated;
use PiovezanFernando\LaravelApiVueForge\Events\GeneratorFileCreating;
use PiovezanFernando\LaravelApiVueForge\Events\GeneratorFileDeleted;
use PiovezanFernando\LaravelApiVueForge\Events\GeneratorFileDeleting;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIControllerGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRequestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIResourceGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRoutesGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APITestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\FactoryGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\FrontQuasarGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\MigrationGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ModelGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryTestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\SeederGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ServiceGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ServiceTestGenerator;
use PiovezanFernando\LaravelApiVueForge\Utils\GeneratorFieldsInputUtil;
use PiovezanFernando\LaravelApiVueForge\Utils\TableFieldsGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\VarExporter\VarExporter;

class BaseCommand extends Command
{
    public GeneratorConfig $config;

    public Composer $composer;

    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    public function handle()
    {
        $this->config = app(GeneratorConfig::class);
        $this->config->setCommand($this);

        $this->config->init();
        $this->getFields();
    }

    public function generateCommonItems()
    {
        if (!$this->option('fromTable') and !$this->isSkip('migration')) {
            $migrationGenerator = app(MigrationGenerator::class);
            $migrationGenerator->generate();
        }

        if (!$this->isSkip('model')) {
            $modelGenerator = app(ModelGenerator::class);
            $modelGenerator->generate();
        }

        if (!$this->isSkip('repository') && $this->config->options->repositoryPattern) {
            $repositoryGenerator = app(RepositoryGenerator::class);
            $repositoryGenerator->generate();
        }

        if ($this->config->options->factory || (!$this->isSkip('tests') and $this->config->options->tests)) {
            $factoryGenerator = app(FactoryGenerator::class);
            $factoryGenerator->generate();
        }

        if ($this->config->options->seeder) {
            $seederGenerator = app(SeederGenerator::class);
            $seederGenerator->generate();
        }
    }

    public function generateAPIItems()
    {
        if (!$this->isSkip('requests') and !$this->isSkip('api_requests')) {
            $requestGenerator = app(APIRequestGenerator::class);
            $requestGenerator->generate();
        }

        if (!$this->isSkip('controllers') and !$this->isSkip('api_controller')) {
            $controllerGenerator = app(APIControllerGenerator::class);
            $controllerGenerator->generate();
        }

        if (!$this->isSkip('routes') and !$this->isSkip('api_routes')) {
            $routesGenerator = app(APIRoutesGenerator::class);
            $routesGenerator->generate();
        }

        if (!$this->isSkip('tests') and $this->config->options->tests) {
            //            if ($this->config->options->repositoryPattern) {
//                $repositoryTestGenerator = app(RepositoryTestGenerator::class);
//            }

            $apiTestGenerator = app(APITestGenerator::class);
            $apiTestGenerator->generate();
        }

        if ($this->config->options->resources) {
            $apiResourceGenerator = app(APIResourceGenerator::class);
            $apiResourceGenerator->generate();
        }

        if ($this->config->options->servicePattern) {
            $serviceGenerator = app(ServiceGenerator::class);
            $serviceGenerator->generate();
        }

        //        if ($this->config->options->servicePattern && $this->config->options->tests) {
//            $serviceTestGenerator = app(ServiceTestGenerator::class);
//            $serviceTestGenerator->generate();
//        }
    }

    public function generateFrontItems()
    {
        $frontGenerator = app(FrontQuasarGenerator::class);
        $frontGenerator->generate();
    }



    public function performPostActions($runMigration = false)
    {
        if ($this->config->options->saveSchemaFile) {
            $this->saveSchemaFile();
        }

        if ($runMigration) {
            if ($this->option('forceMigrate')) {
                $this->runMigration();
            } elseif (!$this->option('fromTable') and !$this->isSkip('migration')) {
                $requestFromConsole = (php_sapi_name() == 'cli');
                if ($requestFromConsole && $this->confirm(apiforge_nl() . 'Do you want to migrate database? [y|N]', false)) {
                    $this->runMigration();
                }
            }
        }

        if ($this->config->options->localized) {
            $this->saveLocaleFile();
        }

        if (!$this->isSkip('dump-autoload')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
    }

    public function runMigration(): bool
    {
        $migrationPath = config('laravel_api_vue_forge.path.migration', database_path('migrations/'));
        $path = Str::after($migrationPath, base_path()); // get path after base_path
        $this->call('migrate', ['--path' => $path, '--force' => true]);

        return true;
    }

    public function isSkip($skip): bool
    {
        if ($this->option('skip')) {
            return in_array($skip, explode(',', $this->option('skip') ?? ''));
        }

        return false;
    }

    public function performPostActionsWithMigration()
    {
        $this->performPostActions(true);
    }

    protected function saveSchemaFile()
    {
        $fileFields = [];

        foreach ($this->config->fields as $field) {
            $fileFields[] = [
                'name' => $field->name,
                'dbType' => $field->dbType,
                'htmlType' => $field->htmlType,
                'validations' => $field->validations,
                'searchable' => $field->isSearchable,
                'fillable' => $field->isFillable,
                'primary' => $field->isPrimary,
                'inForm' => $field->inForm,
                'inIndex' => $field->inIndex,
                'inView' => $field->inView,
            ];
        }

        foreach ($this->config->relations as $relation) {
            $fileFields[] = [
                'type' => 'relation',
                'relation' => $relation->type . ',' . implode(',', $relation->inputs),
            ];
        }

        $path = config('laravel_api_vue_forge.path.schema_files', resource_path('model_schemas/'));

        $fileName = $this->config->modelNames->name . '.json';

        if (file_exists($path . $fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }
        g_filesystem()->createFile($path . $fileName, json_encode($fileFields, JSON_PRETTY_PRINT));
        $this->comment("\nSchema File saved: ");
        $this->info($fileName);
    }

    protected function saveLocaleFile()
    {
        $locales = [
            'singular' => $this->config->modelNames->name,
            'plural' => $this->config->modelNames->plural,
            'fields' => [],
        ];


        foreach ($this->config->fields as $field) {
            $locales['fields'][$field->name] = $field->description ?? Str::title(str_replace('_', ' ', $field->name));
        }

        $path = lang_path(getenv('APP_LOCALE') . '/models/');

        $fileName = $this->config->modelNames->snakePlural . '.php';

        if (file_exists($path . $fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $locales = VarExporter::export($locales);
        $end = ';' . apiforge_nl();
        $content = "<?php\n\nreturn " . $locales . $end;
        g_filesystem()->createFile($path . $fileName, $content);
        $this->comment("\nModel Locale File saved.");
        $this->info($fileName);
    }

    protected function confirmOverwrite(string $fileName, string $prompt = ''): bool
    {
        $prompt = (empty($prompt))
            ? $fileName . ' already exists. Do you want to overwrite it? [y|N]'
            : $prompt;

        return $this->confirm($prompt, false);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['plural', null, InputOption::VALUE_REQUIRED, 'Plural Model name'],
            ['table', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            ['ignoreFields', null, InputOption::VALUE_REQUIRED, 'Ignore fields while generating from table'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Custom primary key'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['skip', null, InputOption::VALUE_REQUIRED, 'Skip Specific Items to Generate (migration,model,controllers,api_controller,repository,requests,api_requests,routes,api_routes,tests,dump-autoload)'],
            ['relations', null, InputOption::VALUE_NONE, 'Specify if you want to pass relationships for fields'],
            ['forceMigrate', null, InputOption::VALUE_NONE, 'Specify if you want to run migration or not'],
            ['connection', null, InputOption::VALUE_REQUIRED, 'Specify connection name'],
        ];
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
        ];
    }

    public function getFields()
    {
        $this->config->fields = [];

        if (!$this->option('fromTable')) {
            $this->error('This generator only supports --fromTable option. Please specify --fromTable and --table=your_table_name');
            throw new \Exception('Missing --fromTable option');
        }

        $this->parseFieldsFromTable();

        // Validate that fields were actually loaded
        if (empty($this->config->fields)) {
            $this->error('No fields found in table: ' . $this->config->tableName);
            $this->error('Please verify that the table exists and has columns.');
            throw new \Exception('No fields loaded from table');
        }

        $this->info('Loaded ' . count($this->config->fields) . ' fields from table: ' . $this->config->tableName);
    }



    protected function parseFieldsFromTable()
    {
        $tableName = $this->config->tableName;

        $ignoredFields = $this->option('ignoreFields');
        if (!empty($ignoredFields)) {
            $ignoredFields = explode(',', trim($ignoredFields));
        } else {
            $ignoredFields = [];
        }

        $tableFieldsGenerator = new TableFieldsGenerator($tableName, $ignoredFields, $this->config->connection);
        $tableFieldsGenerator->prepareFieldsFromTable();
        $tableFieldsGenerator->prepareRelations();

        $this->config->fields = $tableFieldsGenerator->fields;
        $this->config->relations = $tableFieldsGenerator->relations;
    }

    private function prepareEventsData(): array
    {
        return [
            'modelName' => $this->config->modelNames->name,
            'tableName' => $this->config->tableName,
            'nsModel' => $this->config->namespaces->model,
        ];
    }

    public function fireFileCreatingEvent($commandType)
    {
        event(new GeneratorFileCreating($commandType, $this->prepareEventsData()));
    }

    public function fireFileCreatedEvent($commandType)
    {
        event(new GeneratorFileCreated($commandType, $this->prepareEventsData()));
    }

    public function fireFileDeletingEvent($commandType)
    {
        event(new GeneratorFileDeleting($commandType, $this->prepareEventsData()));
    }

    public function fireFileDeletedEvent($commandType)
    {
        event(new GeneratorFileDeleted($commandType, $this->prepareEventsData()));
    }
}
