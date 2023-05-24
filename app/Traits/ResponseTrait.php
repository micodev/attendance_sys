<?php

namespace App\Traits;

trait ResponseTrait
{
    public function response($code = 200, $message = "default message", $error = [], $result = null, $token = null, $count = null, $meta=null, $pageCount = null)
    {
        if (!is_array($result) && !is_a($result, 'Illuminate\Database\Eloquent\Collection')) {
            if ($result == null) {
                $result = [];
            } else {
                $result = [$result];
            }
        }
        $response = array();
        if (!empty($code)) {
            $response['code'] = $code;
            $response['success'] = ($code >= 200 && $code < 299) ? true : false;
        }
        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($error)) {
            if (!is_array($error) && !is_a($error, 'Illuminate\Database\Eloquent\Collection')) {
                if ($error == null) {
                    $error = [];
                } else {
                    $error = [$error];
                }
            }
            $response['errors'] = $error;
        }
        if (!empty($result)) {
            $response['data'] = $result;
        }
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        if (!empty($token)) {
            $response['token'] = $token;
        }
        if (isset($count)) {
            $response['count'] = $count;
        }
        if (isset($pageCount)) {
            $response['page_count'] = $pageCount;
        }

        return response()->json($response, $code);
    }
}
