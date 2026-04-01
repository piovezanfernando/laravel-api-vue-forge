@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->services }};

use {{ $config->namespaces->app }}\Repositories\{{ $config->modelNames->name }}Repository;

class {{ $config->modelNames->name }}Service extends BaseService
{
    public function __construct(protected readonly {{ $config->modelNames->name }}Repository $repository)
    {
    }
}
