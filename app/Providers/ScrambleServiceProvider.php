<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\SecuritySchemes\OAuthFlow;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\ServiceProvider;

class ScrambleServiceProvider extends ServiceProvider
{
  public function boot()
  {
    Scramble::configure()
      ->expose(
        ui: '/api/documentation',
        document: '/docs/api.json',
      )
      ->withDocumentTransformers(function (OpenApi $openApi) {

        // INFO: define security schemas
        $openApi->secure(SecurityScheme::http('basic')->as("basicAuth"));
        $openApi->secure(SecurityScheme::oauth2()
          ->as("passport")
          ->flow('clientCredentials', function (OAuthFlow $flow) {
            $flow
              ->tokenUrl(config('app.url') . '/oauth/token')
              ->addScope('*', 'all');
          }));
      })

      ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo) {

        // INFO: assign security schema based on middleware
        $routeMiddlewares = collect($routeInfo->route->gatherMiddleware());
        if ($routeMiddlewares->contains("basicAuth")) {
          $operation->addSecurity(new SecurityRequirement(["basicAuth" => []]));
        } elseif ($routeMiddlewares->contains("client")) {
          $operation->addSecurity(new SecurityRequirement(["passport" => []]));
        } else {
          $operation->security = [];
        }
      })
    ;
  }
}
