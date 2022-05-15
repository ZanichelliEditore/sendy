<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 *@OA\Info(
 *    version="1.0.0",
 *    title="API to send email",
 *    description="REST API to send email",
 *    @OA\Contact(
 *         name="DEV team"
 *    )
 *)
 *
 * @OA\Server(
 *  url=L5_APP_URL
 * )
 *
 * @OA\Tag(
 *  name="email",
 *  description="Service used to send one email"
 * )
 *
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     name="passport",
 *     securityScheme="passport",
 *     in="header",
 *     scheme={"http","https"},
 *     @OA\Flow(
 *         flow="clientCredentials",
 *         tokenUrl=L5_SWAGGER_CONST_HOST,
 *         scopes={}
 *     )
 *  )
 *
 * @OA\Components(
 *      @OA\RequestBody(
 *         request="Mail",
 *         description="Mail object that needs to be send",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/Mail")
 *         )
 *      ),
 *      @OA\Response(
 *         response="Error500",
 *         description="Internal Server Error",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/Message500")
 *         )
 *     ),
 *     @OA\Response(
 *         response="Error404",
 *         @OA\MediaType(mediaType="application/json"),
 *         description="Not Found"
 *     ),
 *     @OA\Response(
 *         response="Success200",
 *         description="Operation successful",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/Message200")
 *         )
 *     ),
 *     @OA\Response(
 *         response="Success201",
 *         @OA\MediaType(mediaType="application/json"),
 *         description="Created"
 *     ),
 *     @OA\Response(
 *         response="Error401",
 *         description="Unauthenticated",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/Message401")
 *         )
 *     ),
 *     @OA\Response(
 *         response="Error413",
 *         @OA\MediaType(mediaType="application/json"),
 *         description="Request too large."
 *     ),
 *     @OA\Response(
 *         response="Error422",
 *         @OA\MediaType(mediaType="application/json"),
 *         description="Unprocessable entity: data validation error"
 *     ),
 *     @OA\Schema(
 *          schema="Message200",
 *          type="object",
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              example="Email sent with success"
 *          )
 *      ),
 *      @OA\Schema(
 *          schema="Message401",
 *          type="object",
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              default="Unauthenticated request."
 *          )
 *      ),
 *     @OA\Schema(
 *         schema="Message404",
 *         type="object",
 *         @OA\Property(
 *             property="message",
 *             type="string",
 *             default="Object not found"
 *         )
 *     ),
 *     @OA\Schema(
 *          schema="Message500",
 *          type="object",
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              default="System error"
 *          )
 *      ),
 *     @OA\Schema(
 *         schema="Mail",
 *         type="object",
 *         required={"from","to"},
 *         @OA\Property(
 *             property="from",
 *             type="string",
 *             example="noreply@email.it"
 *         ),
 *         @OA\Property(
 *             property="to",
 *             type="array",
 *             @OA\Items(
 *                type="string",
 *             ),
 *             example={"receiverOne@email.it"}
 *         ),
 *         @OA\Property(
 *             property="cc",
 *             type="array",
 *             @OA\Items(
 *                type="string",
 *                example="ccOne@email.it"
 *             ),
 *         ),
 *         @OA\Property(
 *             property="bcc",
 *             type="array",
 *             @OA\Items(
 *                type="string",
 *                example="bccOne@email.it"
 *             ),
 *         ),
 *         @OA\Property(
 *             property="subject",
 *             type="string",
 *             example="subject of one email"
 *         ),
 *         @OA\Property(
 *             property="body",
 *             type="string",
 *             example="questo è il messaggio della email"
 *         ),
 *         @OA\Property(
 *             property="attachments",
 *             type="array",
 *             @OA\Items(
 *                type="string",
 *                format="binary"
 *             )
 *         )
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    const PAGINATION = 12;
}