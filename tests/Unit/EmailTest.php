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
    private function createEmail(bool $file = false)
    {
        return new Email(
            'sender@example.com',
            ['receiver1@example.com', 'receiver2@example.com'],
            ['receiverCC@example.com'],
            ['receiverBCC@example.com'],
            'Subject',
            'Fake body',
            ($file ? 'Fake_directory' : null)
        );
    }

    /**
     * Return field from array
     *
     * @param array $emailField
     * @return array
     */
    private function mapEmailObject($emailField)
    {
        return array_map(function ($m) {
            return $m['address'];
        }, $emailField);
    }

    /**
     * @test
     * @return void
     */
    public function sendEmailSuccessfully()
    {
        Mail::fake();
        $mail = $this->createEmail();
        $objectMail = new CustomEmail($mail);

        // the listener for this event sends mail
        EmailSender::dispatch($objectMail);


        Mail::assertSent(CustomEmail::class, function ($email) use ($objectMail) {
            $email->build();
            $mailArray = $objectMail->getEmail();

            return $this->mapEmailObject($email->to) === $mailArray->getTo() &&
                $this->mapEmailObject($email->cc) === $mailArray->getCc() &&
                $this->mapEmailObject($email->bcc) === $mailArray->getBcc() &&
                $this->mapEmailObject($email->from) === [$mailArray->getFrom()];
        });
    }

    /**
     * @test
     * @return void
     */
    public function sendEmailWithAttachment()
    {
        Mail::fake();
        Storage::fake('local');

        Storage::put('attachments/Fake_directory/test.txt', 'hello');
        // dd(Storage::allFiles());
        $mail = $this->createEmail(true);
        $objectMail = new CustomEmail($mail);
        EmailSender::dispatch($objectMail);
        Mail::assertSent(CustomEmail::class, function ($email) use ($objectMail) {
            $email->build();
            $mailArray = $objectMail->getEmail();

            return $this->mapEmailObject($email->to) === $mailArray->getTo() &&
                $this->mapEmailObject($email->cc) === $mailArray->getCc() &&
                $this->mapEmailObject($email->bcc) === $mailArray->getBcc() &&
                $this->mapEmailObject($email->from) === [$mailArray->getFrom()];
        });
    }
}
