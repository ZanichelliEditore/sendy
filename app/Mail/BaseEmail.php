<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Storage;

abstract class BaseEmail extends Mailable
{
    protected $email;
    protected $size;

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
        $builder = $this->from($this->email->getFrom(), $this->email->getSender())
            ->to($this->email->getTo())
            ->cc($this->email->getCc())
            ->bcc($this->email->getBcc())
            ->subject($this->email->getSubject())
            ->view($this->useView())
            ->with(
                [
                    'body' => $this->email->getBody()
                ]
            );

        $directory = $this->email->getAttachmentsDirectory();

        if (!empty($directory)) {
            foreach (Storage::files('attachments/' . $directory) as $file) {
                $builder->attachFromStorage($file);
            }
        }

        return $builder;
    }

    public function saveAttachments($attachments)
    {
        if (!$this->size) $this->calcSize();

        $directory = $this->email->getAttachmentsDirectory();
        foreach ($attachments as $file) {
            $this->size += $file->getSize();
            Storage::putFileAs('attachments/' . $directory, $file, $file->getClientOriginalName());
        }
    }


    public function getSize()
    {
        return $this->size;
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


    public function calcSize(): int
    {
        $headers = array(
            'from' => $this->email->getFrom(),
            'sender' => $this->email->getSender(),
            'to' => $this->email->getTo(),
            'cc' => $this->email->getCc(),
            'bcc' => $this->email->getBcc(),
            'subject' => $this->email->getSubject()
        );

        $body = $this->email->getBody();
        $size = strlen($body);

        if ($imgesSize = $this->getImagesSize($body)) $size += $imgesSize;

        foreach ($headers as $header) {
            if (is_array($header)) $size += strlen(implode('', $header));
            else $size += strlen($header);
        }

        return $this->size = $size;
    }

    /**
     * Returns the view to use.
     *
     * @return string
     */
    protected abstract function useView();


    private function getImagesSize(string $body): int|null
    {
        preg_match_all('/(<img)[[:word:][:punct:][:space:]]*(src=")[[:word:][:punct:]]*"/m', $body, $matches);

        if (empty($matches)) return null;

        $size = 0;
        foreach ($matches[0] as $match) {
            $filename = str_replace('"', '', explode('src="', $match))[1];

            //INFO: get image size info
            extract(
                getimagesize($filename),
                EXTR_PREFIX_INVALID,
                'dimension'
            );

            //INFO: size = (width * height * BPP) / bit per byte
            $size += ($dimension_0 * $dimension_1 * $bits) / 8;
        }
        return $size;
    }
}
