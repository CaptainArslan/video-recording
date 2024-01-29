<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseHelper
{
    /**
     * return json response with success message
     *
     * @param  array|object  $data
     * @param  string  $message
     * @param  array  $headers
     * @param  int  $statusCode
     * @return void
     */
    public static function respondWithSuccess($data = null, $message = null, $headers = [], $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode, $headers);
    }

    /**
     * return json response with error message
     *
     * @param  string  $message
     * @param  int  $code
     * @return void
     */
    public static function respondWithError($message = null, $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
