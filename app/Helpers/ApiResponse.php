<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error($message = 'Error', $error = [], $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'error' => $error
        ], $code);
    }
}
