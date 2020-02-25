<?php

namespace App\Jobs;

use App\Mail\BaseEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const QUEUE_NAME = 'emails';

    private $mailable;
    /**
     * Create a new job instance.
     *
     * @param BaseEmail $mailable
     */
    public function __construct(BaseEmail $mailable)
    {
        $this->mailable = $mailable;
        $this->onQueue(self::QUEUE_NAME);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send email
        Mail::send($this->mailable);

        // Delete attachments
        $emailInfo = $this->mailable->getEmail();

        if ($attachmentsDirectory = $emailInfo->getAttachmentsDirectory()) {
            Storage::deleteDirectory('attachments/' . $attachmentsDirectory);
        }
    }

    public function getMailable()
    {
        return $this->mailable;
    }

}
