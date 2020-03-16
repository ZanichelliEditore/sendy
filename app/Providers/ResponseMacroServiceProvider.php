<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register the application's response macros.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('success200', function ($value = "", $params = []) {
            $params['content'] = $value;
            Log::info('200* ' . json_encode($params));
            if ($value) {
                return Response::make( ['message' => $value], 200);
            }
            return Response::make('', 200);
        });

        Response::macro('success201', function ($value = "", $type, $object = null) {
            if ($value && $object) {
                return Response::make( [
                    'message' => $value,
                    $type => $object
                ], 201);
            }
            return Response::make('', 201);
        });

        Response::macro('success204', function () {
            return Response::make('', 204);
        });

        Response::macro('error404', function ($value = '') {
            $message = ($value? $value : __('messages.Object')) . __('messages.NotFound');
            Log::error('404* ' . json_encode(['content' => $message]));
            return Response::make( ['message' => $message], 404);
        });

        Response::macro('error422', function ($field, $error) {

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
        });

        Response::macro('error500', function ($value = '') {
            $message = $value? $value : __('messages.SystemError');
            Log::error('500* ' . json_encode(['content' => $message]));
            return Response::make( ['message' => $message], 500);
        });

    }
}
