<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Email;
use App\Jobs\EmailSender;
use App\Mail\CustomEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailTest extends TestCase
{
    private function createEmail(bool $file = false, bool $withSender = false, bool $withReplyTo = false)
    {
        return new Email(
            'from@example.com',
            ['receiver1@example.com', 'receiver2@example.com'],
            ['receiverCC@example.com'],
            ['receiverBCC@example.com'],
            $withSender ? "sender" : null,
            $withReplyTo ? 'replyTo@example.com' : null,
            'Subject',
            'Fake body',
            $file ? 'Fake_directory' : null
        );
    }

    public function testSendEmailSuccessfully()
    {
        Mail::fake();
        $mail = $this->createEmail();
        $objectMail = new CustomEmail($mail);

        // the listener for this event sends mail
        EmailSender::dispatch($objectMail);


        Mail::assertSent(CustomEmail::class, function ($email) use ($objectMail) {
            $email->build();
            $mailArray = $objectMail->getEmail();

            return
                $email->hasTo($mailArray->getTo()) &&
                $email->hasCc($mailArray->getCc()) &&
                $email->hasBcc($mailArray->getBcc()) &&
                $email->hasFrom($mailArray->getFrom()) &&
                $email->subject == $mailArray->getSubject() &&
                $email->viewData['body'] == $mailArray->getBody();
        });
    }

    public function testSendEmailWithAttachment()
    {
        Mail::fake();

        $fakeFile = 'attachments/Fake_directory/file.txt';
        Storage::shouldReceive('files')->andReturn([$fakeFile]);
        Storage::shouldReceive('deleteDirectory');

        $mail = $this->createEmail(true);
        $objectMail = new CustomEmail($mail);
        EmailSender::dispatch($objectMail);
        Mail::assertSent(CustomEmail::class, function ($email) use ($objectMail, $fakeFile) {
            $email->build();

            return $email->diskAttachments[0]['path'] == $fakeFile &&
                $email->diskAttachments[0]['name'] == basename($fakeFile);
        });
    }

    public function testSendEmailWithSender()
    {
        Mail::fake();
        $mail = $this->createEmail(false, true, false);
        $objectMail = new CustomEmail($mail);

        // the listener for this event sends mail
        EmailSender::dispatch($objectMail);


        Mail::assertSent(CustomEmail::class, function ($email) use ($objectMail) {
            $email->build();
            $mailArray = $objectMail->getEmail();

            return
                $email->hasTo($mailArray->getTo()) &&
                $email->hasCc($mailArray->getCc()) &&
                $email->hasBcc($mailArray->getBcc()) &&
                $email->hasFrom($mailArray->getFrom(), $mailArray->getSender()) &&
                $email->subject == $mailArray->getSubject() &&
                $email->viewData['body'] == $mailArray->getBody();
        });
    }

    public function testSendEmailWithReplyTo()
    {
        Mail::fake();
        $mail = $this->createEmail(false, false, true);
        $objectMail = new CustomEmail($mail);

        // the listener for this event sends mail
        EmailSender::dispatch($objectMail);


        Mail::assertSent(CustomEmail::class, function ($email) use ($objectMail) {
            $email->build();
            $mailArray = $objectMail->getEmail();

            return
                $email->hasTo($mailArray->getTo()) &&
                $email->hasCc($mailArray->getCc()) &&
                $email->hasBcc($mailArray->getBcc()) &&
                $email->hasFrom($mailArray->getFrom()) &&
                $email->hasReplyTo($mailArray->getReplyTo()) &&
                $email->subject == $mailArray->getSubject() &&
                $email->viewData['body'] == $mailArray->getBody();
        });
    }
}
