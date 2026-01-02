<?php

use Illuminate\Support\Str;
use PiovezanFernando\LaravelApiVueForge\Common\FileSystem;

if (!function_exists('g_filesystem')) {
    /**
     * @return FileSystem
     */
    function g_filesystem()
    {
        return app(FileSystem::class);
    }
}

if (!function_exists('apiforge_tab')) {
    function apiforge_tab(int $spaces = 4): string
    {
        return str_repeat(' ', $spaces);
    }
}

if (!function_exists('apiforge_tabs')) {
    function apiforge_tabs(int $tabs, int $spaces = 4): string
    {
        return str_repeat(apiforge_tab($spaces), $tabs);
    }
}

if (!function_exists('apiforge_nl')) {
    function apiforge_nl(int $count = 1): string
    {
        return str_repeat(PHP_EOL, $count);
    }
}

if (!function_exists('apiforge_nls')) {
    function apiforge_nls(int $count, int $nls = 1): string
    {
        return str_repeat(apiforge_nl($nls), $count);
    }
}

if (!function_exists('apiforge_nl_tab')) {
    function apiforge_nl_tab(int $lns = 1, int $tabs = 1): string
    {
        return apiforge_nls($lns).apiforge_tabs($tabs);
    }
}

if (!function_exists('model_name_from_table_name')) {
    function model_name_from_table_name(string $tableName): string
    {
        return Str::ucfirst(Str::camel(Str::singular($tableName)));
    }
}

if (!function_exists('create_resource_route_names')) {
    function create_resource_route_names($name, $isScaffold = false): array
    {
        $result = [
            "'index' => '$name.index'",
            "'store' => '$name.store'",
            "'show' => '$name.show'",
            "'update' => '$name.update'",
            "'destroy' => '$name.destroy'",
        ];

        if ($isScaffold) {
            $result[] = "'create' => '$name.create'";
            $result[] = "'edit' => '$name.edit'";
        }

        return $result;
    }
}

if (!function_exists('get_field_length')) {
    function get_field_length($type): string
    {
        preg_match('/\(\s*(\d+(?:,\s*\d+)*)\s*\)/', $type, $matches);
        return $matches[1] ?? 0;
    }
}

if (!function_exists('get_field_precision')) {
    function get_field_precision($length): int
    {
        $precision = explode(',', $length);
        return $precision[0] ?? 0;
    }
}

if (!function_exists('get_field_scale')) {
    function get_field_scale($length): int
    {
        $precision = explode(',', $length);
        return $precision[1] ?? 0;
    }
}
