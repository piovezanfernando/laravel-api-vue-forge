@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Http\Controllers;

use InfyOm\Generator\Utils\ResponseUtil;

/**
 * @OA\Server(url="/{{ $apiPrefix }}")
 * @OA\Info(
 *   title="InfyOm Laravel Generator APIs",
 *   version="1.0.0"
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
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
    public function response(Array $message): JsonResponse
    {
        if ($message['code'] == 200) {
            return Response::json(ResponseUtil::makeResponse($message['message'], []));
        }

        return Response::json(ResponseUtil::makeError($message['message']), $message['code']);
        return response()->json([
            'success' => true,
            'message' => $message
        ], 200);
    }
}
