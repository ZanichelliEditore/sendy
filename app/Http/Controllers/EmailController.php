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
