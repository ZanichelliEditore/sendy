<?php

namespace App\Jobs;

use Exception;
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

    private const QUEUE_NAME = 'emails';

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 60;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

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

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::error("CUSTOM-ERROR: Error on processing job " . $exception->getMessage());
    }

    public function getMailable()
    {
        return $this->mailable;
    }
}
