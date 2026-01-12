<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands\Publish;

use PiovezanFernando\LaravelApiVueForge\Commands\BaseCommand;

class SetupSpaCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiforge:setup-spa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the SPA route in Laravel to serve the frontend';

    public function handle()
    {
        $this->publishSpaView();
        $this->updateWebRoutes();
        $this->createSymlinks();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
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

    private function publishSpaView()
    {
        $viewPath = resource_path('views/spa.blade.php');

        if (file_exists($viewPath) && !$this->confirmOverwrite('spa.blade.php')) {
            return;
        }

        $templateData = view('laravel-api-vue-forge::front.spa')->render();

        g_filesystem()->createFile($viewPath, $templateData);

        $this->info('spa.blade.php created in resources/views');
    }

    private function updateWebRoutes()
    {
        $webRoutesPath = base_path('routes/web.php');

        if (!file_exists($webRoutesPath)) {
            $this->error("Web routes file not found at $webRoutesPath");
            return;
        }

        $webRoutesContent = g_filesystem()->getFile($webRoutesPath);

        // Define the root wildcard route for the SPA
        $spaRoute = "Route::get('{any?}', function () {" . PHP_EOL . "    return view('spa');" . PHP_EOL . "})->where('any', '.*');";

        if (str_contains($webRoutesContent, "view('spa')")) {
            $this->info('SPA route already exists in web.php');
            return;
        }

        // Suggest replacing the default welcome route if it exists
        if (str_contains($webRoutesContent, "view('welcome')")) {
            if ($this->confirm("Found default 'welcome' route. Do you want to replace it with the SPA root route?", true)) {
                $webRoutesContent = preg_replace("/Route::get\('\/', function \(\) \{.*?return view\('welcome'\);.*?\}\);/s", $spaRoute, $webRoutesContent);
            } else {
                $webRoutesContent .= PHP_EOL . PHP_EOL . $spaRoute . PHP_EOL;
            }
        } else {
            $webRoutesContent .= PHP_EOL . PHP_EOL . $spaRoute . PHP_EOL;
        }

        g_filesystem()->createFile($webRoutesPath, $webRoutesContent);

        $this->info('SPA route added/updated in routes/web.php');
    }

    private function createSymlinks()
    {
        $frontendPath = config('laravel_api_vue_forge.path.frontend', base_path('front/'));
        $spaDistPath = $frontendPath . 'dist/spa';
        $publicPath = public_path();

        if (!file_exists($spaDistPath)) {
            $this->warn("SPA dist path not found: $spaDistPath. Please build the frontend first.");
            return;
        }

        // List of specific folders/files to link to the public directory
        $links = [
            'assets' => $spaDistPath . DIRECTORY_SEPARATOR . 'assets',
            'icons' => $spaDistPath . DIRECTORY_SEPARATOR . 'icons',
            'favicon.ico' => $spaDistPath . DIRECTORY_SEPARATOR . 'favicon.ico',
        ];

        // Clean up old potential symlinks that might cause conflicts
        $oldLinks = ['front', 'app-assets'];
        foreach ($oldLinks as $oldLink) {
            $oldPath = $publicPath . DIRECTORY_SEPARATOR . $oldLink;
            if (is_link($oldPath) || file_exists($oldPath)) {
                $this->info("Cleaning up old symlink/file: $oldLink");
                if (is_dir($oldPath) && !is_link($oldPath)) {
                    g_filesystem()->deleteDirectory($oldPath);
                } else {
                    unlink($oldPath);
                }
            }
        }

        foreach ($links as $linkName => $targetPath) {
            $linkPath = $publicPath . DIRECTORY_SEPARATOR . $linkName;

            if (!file_exists($targetPath)) {
                $this->warn("Target path not found: $targetPath. Skipping symlink for $linkName.");
                continue;
            }

            if (file_exists($linkPath) || is_link($linkPath)) {
                if (is_link($linkPath) && readlink($linkPath) === $targetPath) {
                    $this->info("Symlink for '$linkName' already exists and is correct.");
                    continue;
                }

                if ($this->confirm("File/link already exists at $linkPath. Do you want to replace it with a symlink to $targetPath?", true)) {
                    if (is_dir($linkPath) && !is_link($linkPath)) {
                        g_filesystem()->deleteDirectory($linkPath);
                    } else {
                        unlink($linkPath);
                    }

                    if (symlink($targetPath, $linkPath)) {
                        $this->info("Created symlink: $linkPath -> $targetPath");
                    } else {
                        $this->error("Failed to create symlink: $linkPath");
                    }
                }
            } else {
                if (symlink($targetPath, $linkPath)) {
                    $this->info("Created symlink: $linkPath -> $targetPath");
                } else {
                    $this->error("Failed to create symlink: $linkPath");
                }
            }
        }
    }
}
