<?php

namespace JericIzon\Eypiay\Traits;

trait ResponseApi
{
    /**
     * Send any success response
     *
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode
     */
    public function responseSuccess($message, $data = [], $statusCode = 200)    {
        $jsonResponse = [
            'code' => $statusCode,
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
        return response()->json($jsonResponse, $statusCode);
    }

    public function responseValidationError($error, $statusCode)
    {
        $jsonResponse = [
            'code' => $statusCode,
            'success' => false,
            'message' => $error,
        ];
        return response()->json($jsonResponse, $statusCode);
    }

    public function responseError(\Exception $error, $statusCode = 500)
    {
        \Log::error($error);
        $jsonResponse = [
            'code' => $statusCode,
            'success' => false,
            'message' => $error->getMessage()
        ];

        if(config('app.debug') === false && $statusCode >= 500) {
            $jsonResponse['message'] = 'Sorry, something went wrong.';
        }

        return response()->json($jsonResponse, $statusCode);
    }
}
