<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands\Publish;

use Symfony\Component\Console\Input\InputOption;

class GeneratorPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiforge:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes & init api routes, base controller, base test cases traits.';

    protected $config;

    public function handle()
    {
        $this->config = new \stdClass();
        $this->config->namespaces = (object) [
            'services'    => config('laravel_api_vue_forge.namespace.services', 'App\Services'),
            'apiResource' => config('laravel_api_vue_forge.namespace.api_resource', 'App\Http\Resources\API'),
            'model'       => config('laravel_api_vue_forge.namespace.model', 'App\Models'),
            'repository'  => config('laravel_api_vue_forge.namespace.repository', 'App\Repositories'),
        ];

        $this->publishTestCases();
        $this->publishBaseController();
        $repositoryPattern = config('laravel_api_vue_forge.options.repository_pattern', true);
        $baseModel = config('laravel_api_vue_forge.options.base_model', true);
        $baseService = config('laravel_api_vue_forge.options.base_service', true);
        $baseRequest = config('laravel_api_vue_forge.options.base_request', true);

        if ($repositoryPattern) {
            $this->publishModelCreate();
            $this->publishSearchService();
            $this->publishBaseRepository();
        }
        if ($this->option('localized')) {
            $this->publishLocaleFiles();
        }
        if ($baseModel) {
            $this->publishBelongsToCompany();
            $this->publishBaseModel();
        }
        if ($baseService) {
            $this->publishBaseService();
        }
        if ($baseRequest) {
            $this->publishBaseRequest();
        }
``
        if ($this->confirm('Do you want to setup the SPA route in web.php?', true)) {
            $this->call('apiforge:setup-spa');
        }
    }



    private function publishTestCases()
    {
        $testsPath = config('laravel_api_vue_forge.path.tests', base_path('tests/'));
        $testsNameSpace = config('laravel_api_vue_forge.namespace.tests', 'Tests');
        $createdAtField = config('laravel_api_vue_forge.timestamps.created_at', 'created_at');
        $updatedAtField = config('laravel_api_vue_forge.timestamps.updated_at', 'updated_at');

        $templateData = view('laravel-api-vue-forge::api.test.api_test_trait', [
            'timestamps'      => "['$createdAtField', '$updatedAtField']",
            'namespacesTests' => $testsNameSpace,
        ])->render();

        $fileName = 'ApiTestTrait.php';

        if (file_exists($testsPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createFile($testsPath.$fileName, $templateData);
        $this->info('ApiTestTrait created');

        $testAPIsPath = config('laravel_api_vue_forge.path.api_test', base_path('tests/APIs/'));
        if (!file_exists($testAPIsPath)) {
            g_filesystem()->createDirectoryIfNotExist($testAPIsPath);
            $this->info('APIs Tests directory created');
        }

        $testRepositoriesPath = config('laravel_api_vue_forge.path.repository_test', base_path('tests/Repositories/'));
        if (!file_exists($testRepositoriesPath)) {
            g_filesystem()->createDirectoryIfNotExist($testRepositoriesPath);
            $this->info('Repositories Tests directory created');
        }
    }

    private function publishBaseController()
    {
        $controllerPath = app_path('Http/Controllers/');
        $fileName = 'BaseController.php';

        if (file_exists($controllerPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $templateData = view('laravel-api-vue-forge::stubs.base_controller', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'apiPrefix'    => config('laravel_api_vue_forge.api_prefix'),
            'config'       => $this->config,
        ])->render();

        g_filesystem()->createFile($controllerPath.$fileName, $templateData);

        $this->info('BaseController created');
    }

    private function publishBelongsToCompany()
    {
        $traitsPath = app_path('Traits/');

        $fileName = 'BelongsToCompany.php';

        if (file_exists($traitsPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createDirectoryIfNotExist($traitsPath);

        $templateData = view('laravel-api-vue-forge::stubs.belongs_to_company', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'config'       => $this->config,
        ])->render();

        g_filesystem()->createFile($traitsPath.$fileName, $templateData);

        $this->info('BelongsToCompany trait created');
    }

    private function publishBaseModel()
    {
        $templateData = view('laravel-api-vue-forge::base_model', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'config'       => $this->config,
        ])->render();

        $modelPath = app_path('Models/');
        $fileName = 'BaseModel.php';

        g_filesystem()->createFile($modelPath.$fileName, $templateData);

        $this->info('BaseModel created');
    }

    private function publishBaseService()
    {
        $templateData = view('laravel-api-vue-forge::base_service', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'config'       => $this->config,
        ])->render();

        $modelPath = app_path('Services/');
        $fileName = 'BaseService.php';

        g_filesystem()->createFile($modelPath.$fileName, $templateData);

        $this->info('BaseService created');
    }

    private function publishBaseRequest()
    {
        $templateData = view('laravel-api-vue-forge::base_request', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
        ])->render();

        $modelPath = app_path('Http/Requests/API/');
        $fileName = 'BaseRequest.php';

        g_filesystem()->createFile($modelPath.$fileName, $templateData);

        $this->info('BaseRequest created');
    }

    private function publishModelCreate()
    {
        $repositoryPath = app_path('Repositories/');

        $fileName = 'ModelCreate.php';

        if (file_exists($repositoryPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createDirectoryIfNotExist($repositoryPath);

        $templateData = view('laravel-api-vue-forge::stubs.model_create', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'config'       => $this->config,
        ])->render();

        g_filesystem()->createFile($repositoryPath.$fileName, $templateData);

        $this->info('ModelCreate created');
    }

    private function publishSearchService()
    {
        $servicePath = app_path('Services/');

        $fileName = 'SearchService.php';

        if (file_exists($servicePath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createDirectoryIfNotExist($servicePath);

        $templateData = view('laravel-api-vue-forge::stubs.search_service', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'config'       => $this->config,
        ])->render();

        g_filesystem()->createFile($servicePath.$fileName, $templateData);

        $this->info('SearchService created');
    }

    private function publishBaseRepository()
    {
        $repositoryPath = app_path('Repositories/');

        $fileName = 'BaseRepository.php';

        if (file_exists($repositoryPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createDirectoryIfNotExist($repositoryPath);

        $templateData = view('laravel-api-vue-forge::stubs.base_repository', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'config'       => $this->config,
        ])->render();

        g_filesystem()->createFile($repositoryPath.$fileName, $templateData);

        $this->info('BaseRepository created');
    }

    private function publishLocaleFiles()
    {
        $localesDir = __DIR__.'/../../../locale/';

        $this->publishDirectory($localesDir, lang_path(), 'lang', true);

        $this->comment('Locale files published');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['localized', null, InputOption::VALUE_NONE, 'Localize files.'],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}
