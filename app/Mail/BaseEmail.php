<?php

namespace App\Mail;


use App\Models\Email;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Storage;

abstract class BaseEmail extends Mailable
{
    protected $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $builder = $this->from($this->email->getFrom())
            ->to($this->email->getTo())
            ->cc($this->email->getCc())
            ->bcc($this->email->getBcc())
            ->subject($this->email->getSubject())
            ->view($this->useView())
            ->with([
                'body' => $this->email->getBody()
            ]);

        $directory = $this->email->getAttachmentsDirectory();

        if (!empty($directory)) {
            foreach (Storage::files('attachments/' . $directory) as $file) {
                $builder->attachFromStorage($file);
            }
        }

        return $builder;
    }

    /**
     * Set the subject for the message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildSubject($message)
    {
        if ($this->subject) {
            $message->subject($this->subject);
        }

        return $this;
    }

    /**
     * Get email to send
     *
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the view to use.
     *
     * @return string
     */
    protected abstract function useView();

}