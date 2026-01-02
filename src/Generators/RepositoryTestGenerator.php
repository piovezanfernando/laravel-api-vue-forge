<?php

namespace PiovezanFernando\LaravelApiVueForge\Generators;

class RepositoryTestGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = config('laravel_api_vue_forge.path.repository_test', base_path('tests/Repositories/'));
        $this->fileName = $this->config->modelNames->name.'RepositoryTest.php';
    }

    public function generate()
    {
        $templateData = view('laravel-api-vue-forge::repository.repository_test', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(apiforge_nl().'RepositoryTest created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Repository Test file deleted: '.$this->fileName);
        }
    }
}
