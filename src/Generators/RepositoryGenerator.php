<?php

namespace PiovezanFernando\LaravelApiVueForge\Generators;

class RepositoryGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->repository;
        $this->fileName = $this->config->modelNames->name.'Repository.php';
    }

    public function variables(): array
    {
        return [
            'fieldSearchable' => $this->getSearchableFields(),
        ];
    }

    public function generate()
    {
        $templateData = view('laravel-api-vue-forge::repository.repository', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(apiforge_nl().'Repository created: ');
        $this->config->commandInfo($this->fileName);
    }

    protected function getSearchableFields()
    {
        $searchables = [];

        foreach ($this->config->fields as $field) {
            if ($field->isSearchable && !in_array($field->name, $this->config->options->excludedFillable)) {
                $searchables[] = "'".$field->name."'";
            }
        }

        return implode(','.apiforge_nl_tab(1, 2), $searchables) . ',';
    }

    public function generateFullTextFields()
    {
        $fullText = [];
        foreach ($this->commandData->fields as $field) {
            if ($field->isFullText) {
                $fullText[] = "'".$field->name."'";
            }
        }
        return $fullText;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Repository file deleted: '.$this->fileName);
        }
    }
}
