@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->services }};

use {{ $config->namespaces->repository }}\{{ $config->modelNames->name }}Repository;

class {{ $config->modelNames->name }}Service extends BaseService
{
    public function __construct(protected {{ $config->modelNames->name }}Repository $repository)
    {
    }
}
