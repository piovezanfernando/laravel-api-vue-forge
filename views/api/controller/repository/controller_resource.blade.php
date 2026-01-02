@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiController }};

use App\Exceptions\ValidateDeleteException;
use {{ $config->namespaces->app }}\Http\Controllers\BaseController;
use {{ $config->namespaces->apiRequest }}\Index\IndexAPIRequest;
use {{ $config->namespaces->apiRequest }}\{{ $config->modelNames->name }}APIRequest;
use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->services }}\{{ $config->modelNames->name }}Service;
use Illuminate\Http\JsonResponse;

{!! $docController !!}
class {{ $config->modelNames->name }}APIController extends BaseController
{
    {!! $docDestroy !!}
    public function destroy({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        return $this->response($this->{{$config->modelNames->camel}}Service->delete(${{ $config->modelNames->camel }}));
    }

    {!! $docIndex !!}
    public function index(IndexAPIRequest $request): JsonResponse
    {
        $data = $this->{{$config->modelNames->camel}}Service->search($request);
        $resource = $this->resourceClass::collection($data);

        return $this->sendResponse(
            $resource->response()->getData(true),
            __('messages.retrieved', ['model' => __('models/{{ $config->modelNames->snakePlural }}.plural')])
        );
    }

    {!! $docShow !!}
    public function show({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        $resource = $this->resourceClass::make(${{ $config->modelNames->camel }});

        return $this->sendResponse(
            $resource,
            __('messages.retrieved', ['model' => __('models/{{ $config->modelNames->snakePlural }}.singular')])
        );
    }

    {!! $docStore !!}
    public function store({{ $config->modelNames->name }}APIRequest $request): JsonResponse
    {
        $this->{{$config->modelNames->camel}}Service->setRequest($request);
        ${{ $config->modelNames->camel }} = $this->{{$config->modelNames->camel}}Service->create();
        $resource = $this->resourceClass::make(${{ $config->modelNames->camel }});

        return $this->sendResponse(
            $resource,
            __('messages.saved', ['model' => __('models/{{ $config->modelNames->snakePlural }}.singular')])
        );
    }

    {!! $docUpdate !!}
    public function update({{ $config->modelNames->name }}APIRequest $request, {{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        $this->{{$config->modelNames->camel}}Service->setRequest($request);
        ${{ $config->modelNames->camel }} = $this->{{$config->modelNames->camel}}Service->update(${{ $config->modelNames->camel }});
        $resource = $this->resourceClass::make(${{ $config->modelNames->camel }});

        return $this->sendResponse(
            $resource,
            __('messages.updated', ['model' => __('models/{{ $config->modelNames->snakePlural }}.singular')])
        );
    }
}
