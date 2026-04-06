@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InfyOm\Generator\Utils\ResponseUtil;
use Illuminate\Routing\Controller;

/**
 * @OA\Server(url="/{{ $apiPrefix }}")
 * @OA\Info(
 *   title="API Documentation",
 *   version="1.0.0"
 * )
 * This class should be parent class for other API controllers
 * Class BaseController
 */
#[\AllowDynamicProperties]
class BaseController extends Controller
{
    protected string $resourceClass;

    public function __construct()
    {
        $this->initializeResourceClass();
    }

    /**
     * Magic method used to lazily instantiate the service class on first access
     *
     * @throws \Exception
     */
    public function __get(string $name): mixed
    {
        $expectedName = Str::camel(class_basename($this->serviceClass()));

        if ($name === $expectedName) {
            return $this->{$name} ??= app($this->serviceClass());
        }

        throw new \Exception("Property {$name} does not exist.");
    }

    /**
     * Clean and format the return in JSON pattern
     */
    public function sendResponse(mixed $result, string $message): JsonResponse
    {
        $cleaned = is_array($result)
            ? Arr::except($result, ['first_page_url', 'next_page_url', 'prev_page_url', 'last_page_url', 'path', 'links'])
            : $result;

        return response()->json(ResponseUtil::makeResponse($message, $cleaned));
    }

    /**
     * Format the return as a JSON error response
     */
    public function sendError(string $error, int $code = 404): JsonResponse
    {
        return response()->json(ResponseUtil::makeError($error), $code);
    }

    /**
     * Format a simple success JSON response
     */
    public function sendSuccess(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Encaminha um array de resposta para sucesso ou erro com base na chave 'code'
     *
     * @param array{code: int, message: string} $message
     */
    public function sendResult(array $message): JsonResponse
    {
        return match (true) {
            $message['code'] === 200 => response()->json(ResponseUtil::makeResponse($message['message'], [])),
            default                  => response()->json(ResponseUtil::makeError($message['message']), $message['code']),
        };
    }

    /**
     * Initializes the resourceClass property based on the controller name
     */
    protected function initializeResourceClass(): void
    {
        $modelName = str_replace('APIController', '', class_basename(static::class));
        $baseNamespace = '{{ $namespaceApp }}Http\Resources\API';
        $this->resourceClass = "{$baseNamespace}\\{$modelName}Resource";
    }

    /**
     * Returns the service class based on the controller that is accessing
     *
     * @throws \Exception
     */
    protected function serviceClass(): string
    {
        $callingClass = class_basename(static::class);
        $serviceName = str_replace('APIController', 'Service', $callingClass);
        $serviceClass = "{{ $namespaceApp }}Services\\{$serviceName}";

        if (! class_exists($serviceClass)) {
            throw new \Exception("Class {$serviceClass} not found.");
        }

        return $serviceClass;
    }
}
