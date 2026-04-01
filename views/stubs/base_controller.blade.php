@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Http\Controllers;

use InfyOm\Generator\Utils\ResponseUtil;

use Illuminate\Support\Str;

/**
 * @OA\Server(url="/{{ $apiPrefix }}")
 * @OA\Info(
 *   title="API Documentation",
 *   version="1.0.0"
 * )
 * This class should be parent class for other API controllers
 * Class BaseController
 */
class BaseController extends Controller
{
    /**
     * @var string
     */
    protected string $resourceClass;

    public function __construct()
    {
        $this->initializeResourceClass();
    }

    /**
     * Magic method used to instantiate the service class at the time of use by the methods
     * @throws \Exception
     */
    public function __get(string $name)
    {
        $expectedName = Str::camel(class_basename($this->serviceClass()));

        if ($name === $expectedName) {
            return $this->{$name} ?? ($this->{$name} = app($this->serviceClass()));
        }

        throw new \Exception("Property $name does not exist.");
    }
    /**
     * Clean and format the return in JSON pattern
     */
    public function sendResponse(Mixed $result, String $message): JsonResponse
    {
        unset($result['first_page_url']);
        unset($result['next_page_url']);
        unset($result['prev_page_url']);
        unset($result['last_page_url']);
        unset($result['path']);
        unset($result['links']);
        return response()->json(ResponseUtil::makeResponse($message, $result));
    }

    /**
     * Format the return in JSON pattern
     */
    public function sendError(String $error, int $code = 404): JsonResponse
    {
        return response()->json(ResponseUtil::makeError($error), $code);
    }

    /**
     * Formats the return in JSON pattern, unique for success return
     */
    public function sendSuccess($message): JsonResponse
    {
        return Response::json([
                                  'success' => true,
                                  'message' => $message
                              ]);
    }

    /**
     * Formats the return in JSON pattern, passed the return code in the message array
     */
    public function response(array $message): JsonResponse
    {
        if ($message['code'] == 200) {
            return response()->json(ResponseUtil::makeResponse($message['message'], []));
        }

        return response()->json(ResponseUtil::makeError($message['message']), $message['code']);
    }

    /**
     * Initializes the resourceClass property based on the current route and controller name
     */
    protected function initializeResourceClass(): void
    {
        $modelName = str_replace('APIController', '', class_basename(get_called_class()));
        $baseNamespace = 'App\\Http\\Resources\\API';
        $this->resourceClass = "{$baseNamespace}\\{$modelName}Resource";
    }

    /**
     * Returns the service class based on the controller that is accessing the method
     * @throws \Exception
     */
    protected function serviceClass(): string
    {
        $callingClass = class_basename(get_called_class());
        $serviceName = str_replace('APIController', 'Service', $callingClass);
        $serviceClass = "App\\Services\\$serviceName";

        if (!class_exists($serviceClass)) {
            throw new \Exception("Class $serviceClass not found.");
        }

        return $serviceClass;
    }
}
