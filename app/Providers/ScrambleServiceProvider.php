<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\SecuritySchemes\OAuthFlow;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\RequestBodyObject;
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

        // INFO: improve oauth/token route doc
        if ($operation->path == "oauth/token" && $operation->method == "post") {
          $operation->addRequestBodyObject(RequestBodyObject::make()->setContent(
            'application/json',
            Schema::createFromParameters([
              (new Parameter('grant_type', 'query'))->setSchema(Schema::fromType(new StringType))->example("client_credentials"),
              (new Parameter('client_id', 'query'))->setSchema(Schema::fromType(new StringType))->example("1"),
              (new Parameter('client_secret', 'query'))->setSchema(Schema::fromType(new StringType))->example("secretOAuth2Example"),
              (new Parameter('scope', 'query'))->setSchema(Schema::fromType(new StringType))->example(""),
            ])
          ));
        }
      })
    ;
  }
}
