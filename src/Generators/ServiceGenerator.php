<?php

namespace PiovezanFernando\LaravelApiVueForge\Generators;

class ServiceGenerator extends BaseGenerator
{
    private string $serviceFileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->service . '/';
        $this->serviceFileName = $this->config->modelNames->name . 'Service.php';
    }

    public function generate()
    {
        $this->generateService();
    }

    protected function generateService()
    {
        $templateData = view('laravel-api-vue-forge::services.index', $this->variables())->render();

        g_filesystem()->createFile($this->path . '/' . $this->serviceFileName, $templateData);

        $this->config->commandComment(apiforge_nl().'Service : ' . $this->config->modelNames->name . 'Service.php');
        $this->config->commandInfo($this->serviceFileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path . '/', $this->serviceFileName)) {
            $this->config->commandComment('Service file deleted: '.$this->serviceFileName);
        }
    }
}
