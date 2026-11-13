<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    const PAGINATION = 12;

    public function success200($value = "", $params = [])
    {
        $params['content'] = $value;
        Log::info('200* ' . json_encode($params));
        if ($value) {
            return Response::make(['message' => $value], 200);
        }
        return Response::make('', 200);
    }

    public function success201($value, $type, $object = null)
    {
        if ($value && $object) {
            return Response::make([
                'message' => $value,
                $type => $object
            ], 201);
        }
        return Response::make('', 201);
    }

    public function success204()
    {
        return Response::make('', 204);
    }

    public function error404($value = '')
    {
        $message = ($value ? $value : __('messages.Object')) . __('messages.NotFound');
        Log::error('404* ' . json_encode(['content' => $message]));
        return Response::make(['message' => $message], 404);
    }

    public function error422($field, $error)
    {
        if (!$field) {
            return Response::make(
                [
                    'message' => 'Data is invalid',
                    'errors' => $error
                ],
                422
            );
        }

        return Response::make(
            [
                'message' => 'Data is invalid',
                'errors' => [
                    $field =>  [$error]
                ]
            ],
            422
        );
    }

    public function error500($value = '')
    {
        $message = $value ? $value : __('messages.SystemError');
        Log::error('500* ' . json_encode(['content' => $message]));
        return Response::make(['message' => $message], 500);
    }
}
