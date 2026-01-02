@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiController }};

use {{ $config->namespaces->apiRequest }}\{{ $config->modelNames->name }}\Create{{ $config->modelNames->name }}APIRequest;
use {{ $config->namespaces->apiRequest }}\{{ $config->modelNames->name }}\Update{{ $config->modelNames->name }}APIRequest;
use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->services }}\{{ $config->modelNames->name }}Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use {{ $config->namespaces->app }}\Http\Controllers\AppBaseController;

{!! $docController !!}
class {{ $config->modelNames->name }}APIController extends AppBaseController
{
    public function __construct(private readonly {{ $config->modelNames->name }}Service ${{$config->modelNames->camel}}Service)
    {
    }

    {!! $docDestroy !!}
    public function destroy({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        return $this->response($this->{{$config->modelNames->camel}}Service->delete(${{ $config->modelNames->camel }}));
    }

    {!! $docIndex !!}
    public function index(Request $request): JsonResponse
    {
        $data = $this->{{$config->modelNames->camel}}Service->search($request);

        return $this->sendResponse(
            $data,
            __('messages.retrieved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.plural')])
        );
    }

    {!! $docShow !!}
    public function show({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        return $this->sendResponse(
            ${{ $config->modelNames->camel }}->toArray(),
            __('messages.retrieved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
    }

    {!! $docStore !!}
    public function store(Create{{ $config->modelNames->name }}APIRequest $request): JsonResponse
    {
        $this->{{$config->modelNames->camel}}Service->setRequest($request);

        ${{ $config->modelNames->camel }} = $this->{{$config->modelNames->camel}}Service->create();

        return $this->sendResponse(
            ${{ $config->modelNames->camel }},
            __('messages.saved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
    }

    {!! $docUpdate !!}
    public function update(Update{{ $config->modelNames->name }}APIRequest $request, {{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        $this->{{$config->modelNames->camel}}Service->setRequest($request);
        ${{ $config->modelNames->camel }} = $this->{{$config->modelNames->camel}}Service->update(${{ $config->modelNames->camel }});

        return $this->sendResponse(
            ${{ $config->modelNames->camel }},
            __('messages.updated', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
    }
}
