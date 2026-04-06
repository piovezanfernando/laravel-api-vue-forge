@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Http\Requests\API;

use App\Models\BaseModel;
use PiovezanFernando\LaravelApiVueForge\Request\APIRequest;

abstract class BaseRequest extends APIRequest
{
    /**
     * Provides a detailed description of the expected parameters
     * in the body of an HTTP request.
     */
    public function bodyParameters(): array
    {
        return $this->model()::getFieldDescription();
    }

    /**
     * Configure the Model
     */
    abstract public function model(): string|BaseModel;
}
