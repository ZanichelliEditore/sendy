<?php

namespace App\Models;

/**
 * Class Email represents the email to send.
 * @package App\Models
 *
 */
class Email
{
    private $to;
    private $cc;
    private $bcc;
    private $from;
    private $subject;
    private $body;
    private $attachmentsDirectory;

    public function __construct(string $from, array $to, array $cc = [], array $bcc = [], string $subject = null,
                                string $body = null, string $attachmentsDirectory = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->subject = $subject;
        $this->body = $body;
        $this->attachmentsDirectory = $attachmentsDirectory;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getAttachmentsDirectory()
    {
        return $this->attachmentsDirectory;
    }
}
