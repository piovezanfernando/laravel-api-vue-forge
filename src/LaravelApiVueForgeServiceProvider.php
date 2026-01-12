<?php

namespace PiovezanFernando\LaravelApiVueForge;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use PiovezanFernando\LaravelApiVueForge\Commands\API\APIControllerGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\API\APIGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\API\APIRequestsGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\API\TestsGeneratorCommand;

use PiovezanFernando\LaravelApiVueForge\Commands\Common\MigrationGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Common\ModelGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Common\RepositoryGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Front\FrontGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Publish\GeneratorPublishCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Publish\PublishTablesCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Publish\PublishUserCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Publish\SetupFrontCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\Publish\SetupSpaCommand;
use PiovezanFernando\LaravelApiVueForge\Commands\RollbackGeneratorCommand;
use PiovezanFernando\LaravelApiVueForge\Common\FileSystem;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorConfig;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIControllerGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRequestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIResourceGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APIRoutesGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\API\APITestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\BaseGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\FactoryGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\FrontQuasarGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\MigrationGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ModelGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\RepositoryTestGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\SeederGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ServiceGenerator;
use PiovezanFernando\LaravelApiVueForge\Generators\ServiceTestGenerator;

class LaravelApiVueForgeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $configPath = __DIR__ . '/../config/laravel_api_vue_forge.php';
            $this->publishes([
                $configPath => config_path('laravel_api_vue_forge.php'),
            ], 'laravel-api-vue-forge-config');

            $this->publishes([
                __DIR__ . '/../views' => resource_path('views/vendor/laravel-api-vue-forge'),
            ], 'laravel-api-vue-forge-templates');
        }

        $this->registerCommands();
        $this->loadViewsFrom(__DIR__ . '/../views', 'laravel-api-vue-forge');

        View::composer('*', function ($view) {
            $view->with(['config' => app(GeneratorConfig::class)]);
        });

        Blade::directive('tab', function () {
            return '<?php echo apiforge_tab() ?>';
        });

        Blade::directive('tabs', function ($count) {
            return "<?php echo apiforge_tabs($count) ?>";
        });

        Blade::directive('nl', function () {
            return '<?php echo apiforge_nl() ?>';
        });

        Blade::directive('nls', function ($count) {
            return "<?php echo apiforge_nls($count) ?>";
        });
    }

    private function registerCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            APIGeneratorCommand::class,
            APIControllerGeneratorCommand::class,
            APIRequestsGeneratorCommand::class,
            TestsGeneratorCommand::class,

            MigrationGeneratorCommand::class,
            ModelGeneratorCommand::class,
            RepositoryGeneratorCommand::class,

            GeneratorPublishCommand::class,
            PublishTablesCommand::class,
            PublishUserCommand::class,
            SetupFrontCommand::class,
            SetupSpaCommand::class,

            RollbackGeneratorCommand::class,

            FrontGeneratorCommand::class
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel_api_vue_forge.php', 'laravel_api_vue_forge');

        $this->app->singleton(GeneratorConfig::class, function () {
            return new GeneratorConfig();
        });

        $this->app->singleton(FileSystem::class, function () {
            return new FileSystem();
        });

        $this->app->singleton(MigrationGenerator::class);
        $this->app->singleton(ModelGenerator::class);
        $this->app->singleton(RepositoryGenerator::class);

        $this->app->singleton(APIRequestGenerator::class);
        $this->app->singleton(APIControllerGenerator::class);
        $this->app->singleton(APIRoutesGenerator::class);

        $this->app->singleton(RepositoryTestGenerator::class);
        $this->app->singleton(APITestGenerator::class);

        $this->app->singleton(FactoryGenerator::class);
        $this->app->singleton(SeederGenerator::class);
    }
}
