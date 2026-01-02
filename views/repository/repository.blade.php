@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->repository }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};

class {{ $config->modelNames->name }}Repository extends BaseRepository
{
    /** @var array<int, string> fields that can be used in the search */
    protected array $fieldSearchable = [
        {!! $fieldSearchable !!}
    ];

    /**
     * Return searchable fields
     *
     * @return array<string, string>
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     */
    public function model(): string
    {
        return {{ $config->modelNames->name }}::class;
    }
}
