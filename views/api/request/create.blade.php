@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiRequest }}\{{ $config->modelNames->name }};

use App\Http\Requests\API\BaseAPIRequest;
use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};

class Create{{ $config->modelNames->name }}APIRequest extends BaseAPIRequest
{
    /**
     * Configure the Model
     */
    public function model(): string
    {
        return {{ $config->modelNames->name }}::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            {!! $rules !!}
        ];
    }
}
