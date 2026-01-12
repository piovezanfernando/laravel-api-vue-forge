<?php

namespace PiovezanFernando\LaravelApiVueForge\Commands\Publish;

use Illuminate\Support\Facades\Process;
use PiovezanFernando\LaravelApiVueForge\Commands\BaseCommand;

class SetupFrontCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiforge:setup-front';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the frontend project by cloning the repository';

    public function handle()
    {
        $frontendPath = config('laravel_api_vue_forge.path.frontend', base_path('front/'));
        $repository = config('laravel_api_vue_forge.path.frontend_repository');

        if (empty($repository)) {
            $this->error('Frontend repository URL not configured.');
            return;
        }

        if (file_exists($frontendPath)) {
            if (!$this->confirm("Directory $frontendPath already exists. Do you want to delete it and re-clone?")) {
                return;
            }
            $this->info("Removing existing directory: $frontendPath");
            g_filesystem()->deleteDirectory($frontendPath);
        }

        $this->info("Cloning frontend repository: $repository");

        $process = Process::run("git clone $repository $frontendPath");

        if ($process->successful()) {
            $this->info('Frontend cloned successfully!');

            if ($this->confirm('Do you want to run "npm install" in the frontend directory?', true)) {
                $this->info('Installing dependencies... (this may take a while)');
                $npmProcess = Process::path($frontendPath)->run('npm install');

                if ($npmProcess->successful()) {
                    $this->info('Dependencies installed successfully!');
                } else {
                    $this->error('Failed to install dependencies.');
                    $this->line($npmProcess->errorOutput());
                }
            }
        } else {
            $this->error('Failed to clone repository.');
            $this->line($process->errorOutput());
        }
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
}
