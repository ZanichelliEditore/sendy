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

    public function success201($value, $type, $object)
    {
        $response = [
            'message' => $value,
            $type => $object
        ];
        Log::info('201* ' . json_encode($response));
        return Response::make($response, 201);
    }

    public function success204()
    {
        return Response::make('', 204);
    }

    public function error401($value = '')
    {
        $message = $value;
        Log::error('401* ' . json_encode(['content' => $message]));
        return Response::make(['message' => $message], 401);
    }

    public function error403($value = '', $details = [])
    {
        $message = ($value ? $value : __('messages.Unauthorized'));
        Log::error('403* ' . json_encode([
            'content' => $message,
            'details' => $details
        ]));
        return Response::make(['message' => $message], 403);
    }

    public function error404($value = '')
    {
        $message = ($value ? $value : __('messages.Object')) . __('messages.NotFound');
        Log::error('404* ' . json_encode(['content' => $message]));
        return Response::make(['message' => $message], 404);
    }

    public function error409($value = '', $params = [])
    {
        $params['content'] = $value;
        Log::error('409* ' . json_encode($params));
        return Response::make(['message' => $value], 409);
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
