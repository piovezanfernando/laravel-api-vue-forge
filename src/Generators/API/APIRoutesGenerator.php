<?php

namespace PiovezanFernando\LaravelApiVueForge\Generators\API;

use Illuminate\Support\Str;
use PiovezanFernando\LaravelApiVueForge\Generators\BaseGenerator;

class APIRoutesGenerator extends BaseGenerator
{
    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->apiRoutes;
    }

    public function generate()
    {
        $routeContents = g_filesystem()->getFile($this->path);

        $routes = view('laravel-api-vue-forge::api.routes', $this->variables())->render();

        if (Str::contains($routeContents, $routes)) {
            $this->config->commandInfo(apiforge_nl().'Menu '.$this->config->modelNames->dashedPlural.' already exists, Skipping Adjustment.');

            return;
        }

        $routeContents .= apiforge_nls(2).$routes;

        g_filesystem()->createFile($this->path, $routeContents);

        $this->config->commandComment(apiforge_nl().$this->config->modelNames->dashedPlural.' api routes added.');
    }

    public function rollback()
    {
        $routeContents = g_filesystem()->getFile($this->path);

        $routes = view('laravel-api-vue-forge::api.routes', $this->variables())->render();

        if (Str::contains($routeContents, $routes)) {
            $routeContents = str_replace($routes, '', $routeContents);
            g_filesystem()->createFile($this->path, $routeContents);
            $this->config->commandComment('api routes deleted');
        }
    }
}
