<?php

namespace App\Jobs;

use App\Mail\BaseEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EmailSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    private $mailable;
    /**
     * Create a new job instance.
     *
     * @param BaseEmail $mailable
     */
    public function __construct(BaseEmail $mailable)
    {
        $this->mailable = $mailable;
        $this->onQueue(env('QUEUE_DEFAULT', "emails"));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send email
        try {
            Mail::send($this->mailable);

            // Delete attachments
            $emailInfo = $this->mailable->getEmail();

            if ($attachmentsDirectory = $emailInfo->getAttachmentsDirectory()) {
                Storage::deleteDirectory('attachments/' . $attachmentsDirectory);
            }
        } catch (\Swift_SwiftException $e) {
            try {
                Log::channel("slack")->error(":poop: Errore in fase di invio mail: " . $e->getMessage());
            } catch (\Exception $e) {
            }
            Log::error("Swift_SwiftException catched - " . $e->getMessage() . " - Restarting connection");
            Mail::mailer()->forceReconnection();
            throw $e;
        }
    }

    public function getMailable()
    {
        return $this->mailable;
    }
}
