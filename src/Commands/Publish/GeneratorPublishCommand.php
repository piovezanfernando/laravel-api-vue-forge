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

    public function handle()
    {
        $this->updateRouteServiceProvider();
        $this->publishTestCases();
        $this->publishBaseController();
        $repositoryPattern = config('laravel_api_vue_forge.options.repository_pattern', true);
        $baseModel = config('laravel_api_vue_forge.options.base_model', true);
        $baseService = config('laravel_api_vue_forge.options.base_service', true);
        $baseRequest = config('laravel_api_vue_forge.options.base_request', true);

        if ($repositoryPattern) {
            $this->publishBaseRepository();
        }
        if ($this->option('localized')) {
            $this->publishLocaleFiles();
        }
        if ($baseModel) {
            $this->publishBaseModel();
        }
        if ($baseService) {
            $this->publishBaseService();
        }
        if ($baseRequest) {
            $this->publishBaseRequest();
        }

        if ($this->confirm('Do you want to setup the SPA route in web.php?', true)) {
            $this->call('apiforge:setup-spa');
        }
    }

    private function updateRouteServiceProvider()
    {
        $routeServiceProviderPath = app_path('Providers'.DIRECTORY_SEPARATOR.'RouteServiceProvider.php');

        if (!file_exists($routeServiceProviderPath)) {
            $this->error("Route Service provider not found on $routeServiceProviderPath");

            return;
        }

        $fileContent = g_filesystem()->getFile($routeServiceProviderPath);

        $search = "Route::middleware('api')".apiforge_nl().str(' ')->repeat(16)."->prefix('api')";
        $beforeContent = str($fileContent)->before($search);
        $afterContent = str($fileContent)->after($search);

        $finalContent = $beforeContent.$search.apiforge_nl().str(' ')->repeat(16)."->as('api.')".$afterContent;
        g_filesystem()->createFile($routeServiceProviderPath, $finalContent);
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
        $fileName = 'AppBaseController.php';

        if (file_exists($controllerPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $templateData = view('laravel-api-vue-forge::stubs.app_base_controller', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'apiPrefix'    => config('laravel_api_vue_forge.api_prefix'),
        ])->render();

        g_filesystem()->createFile($controllerPath.$fileName, $templateData);

        $this->info('AppBaseController created');
    }

    private function publishBaseModel()
    {
        $templateData = view('laravel-api-vue-forge::app_base_model', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
        ])->render();

        $modelPath = app_path('Models/');
        $fileName = 'BaseModel.php';

        g_filesystem()->createFile($modelPath.$fileName, $templateData);

        $this->info('AppBaseModel created');
    }

    private function publishBaseService()
    {
        $templateData = view('laravel-api-vue-forge::app_base_service', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
        ])->render();

        $modelPath = app_path('Services/');
        $fileName = 'BaseService.php';

        g_filesystem()->createFile($modelPath.$fileName, $templateData);

        $this->info('AppBaseService created');
    }

    private function publishBaseRequest()
    {
        $templateData = view('laravel-api-vue-forge::app_base_request', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
        ])->render();

        $modelPath = app_path('Requests/API/');
        $fileName = 'BaseAPIRequest.php';

        g_filesystem()->createFile($modelPath.$fileName, $templateData);

        $this->info('AppBaseService created');
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
