@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->services }};

use App\Repositories\BaseRepository;
use {{ $config->namespaces->app }}\Repositories\{{ $config->modelNames->name }}Repository;
use Illuminate\Http\Request;

class {{ $config->modelNames->name }}Service extends BaseService
{
    protected Request $request;

    /**
     * Configure the Repository
     */
    public function repo(): string|BaseRepository
    {
        return {{ $config->modelNames->name }}Repository::class;
    }
}
