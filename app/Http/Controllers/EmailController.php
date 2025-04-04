<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Jobs\EmailSender;
use App\Mail\CustomEmail;
use Illuminate\Support\Str;
use App\Http\Requests\EmailRequest;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/emails",
     *     summary="Receive data about one email",
     *     tags={"email"},
     *     security={{"passport":{}}},
     *     description="Use to send data about one email",
     *     operationId="EmailController.send",
     *     @OA\RequestBody(
     *          ref="#/components/requestBodies/Mail"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/Error500"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/Error401"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/responses/Error422"
     *     ),
     *     @OA\Response(
     *         response=413,
     *         ref="#/components/responses/Error413"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         ref="#/components/responses/Success200"
     *     )
     * )
     *
     * @param EmailRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function send(EmailRequest $request)
    {
        $from = $request->input('from');
        $sender = $request->input('sender');
        $to = $request->input('to');
        $cc = $request->input('cc', []);
        $bcc = $request->input('bcc', []);
        $replyTo = $request->input('replyTo');
        $subject = $request->input('subject');
        $body = $request->input('body');
        $attachments = $request->file('attachments');
        $attachmentsDirectory = null;
        if (!empty($attachments)) {
            $attachmentsDirectory = $this->saveAttachments($attachments);
            if (!$attachmentsDirectory) {
                return response([
                    "message" => '',
                    "errors" => [
                        "attachments" => "size of all file is too large"
                    ]
                ], 422);
            }
        }


        $email = new Email($from, $to, $cc, $bcc, $sender, $replyTo, $subject, $body, $attachmentsDirectory);

        $mailable = new CustomEmail($email);

        EmailSender::dispatch($mailable);

        return response([
            "message" => __('messages.EmailTakenOver')
        ], 200);
    }

    /**
     * @param string | array $data
     * @return string|null
     */
    private function saveAttachments($data)
    {

        $size = 0;

        foreach ($data as $file) {
            $size += $file->getSize();
        }

        if ($size > 26214400) {
            return false;
        }

        $directoryName = Str::random(20);
        foreach ($data as $file) {
            Storage::putFileAs('attachments/' . $directoryName, $file, $file->getClientOriginalName());
        }

        return $directoryName;
    }
}
