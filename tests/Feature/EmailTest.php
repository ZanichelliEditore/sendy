<?php

namespace Tests\Feature;

use App\Jobs\EmailSender;
use App\Mail\CustomEmail;
use App\Models\Email;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class EmailTest extends TestCase
{
    use WithoutMiddleware;

    private function createEmail()
    {
        return new Email(
            'sender@example.com',
            ['receiver1@example.com', 'receiver2@example.com'],
            ['receiverCC@example.com'],
            ['receiverBCC@example.com'],
            'Subject',
            'Fake body',
            'Fake directory'
        );
    }

    /**
     * body email
     *
     * @return array
     */
    public function getEmail()
    {
        return [
            'to'        => ['prova@example.com', 'prova2@example.com'],
            'cc'        => ['cc@example.com', 'cc2@example.com'],
            'bcc'       => ['bcc@example.com', 'bcc2@example.com'],
            'from'      => 'test@email.it',
            'subject'   => 'oggetto',
            'body'      => 'corpo del messaggio',
            'attachments' => [UploadedFile::fake()->image('photo1.jpg')],
        ];
    }

    /**
     * @test
     * @return void
     */
    public function sendEmailSuccessfully()
    {
        Bus::fake();
        Storage::fake('local');

        $email = $this->getEmail();

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        $attachmentsDirectory = null;
        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email, &$attachmentsDirectory) {
            $emailInfo = $job->getMailable()->getEmail();
            $attachmentsDirectory = $emailInfo->getAttachmentsDirectory();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });

        Storage::disk('local')->assertExists('attachments/' . $attachmentsDirectory . '/photo1.jpg');
    }

    /**
     * @test
     * @return void
     */
    public function testInvalidReceiver()
    {
        Bus::fake();

        $email = $this->getEmail();
        unset($email['to']);

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "message",
            "errors" => [
                "to"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);
    }

    /**
     * @test
     * @return void
     */
    public function testInvalidSender()
    {
        Bus::fake();

        $email = $this->getEmail();
        unset($email['from']);
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "message",
            "errors" => [
                "from"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);
    }

    /**
     * @test
     * @return void
     */
    public function sendEmail()
    {
        Bus::fake();
        Storage::fake('local');

        Bus::assertNotDispatched(EmailSender::class);

        $response = $this->json('POST', '/api/v1/emails', $this->getEmail());
        $this->assertEquals(200, $response->status());

        $email = $this->getEmail();

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email, &$attachmentsDirectory) {
            $emailInfo = $job->getMailable()->getEmail();
            $attachmentsDirectory = $emailInfo->getAttachmentsDirectory();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });
    }

    /**
     * @test
     * @return void
     */
    public function postToErrorValidationTest()
    {
        Bus::fake();

        $email = $this->getEmail();
        unset($email['to']);
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "message",
            "errors" => [
                "to"
            ]
        ]);

        $email['to'] = ["test"];
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "message",
            "errors" => [
                "to.0"
            ]
        ]);

        $email['to'] = ['just"not"right@example.com'];
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "message",
            "errors" => [
                "to.0"
            ]
        ]);

        $email['to'] = ['test@email.com?'];
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "message",
            "errors" => [
                "to.0"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);
    }

    /**
     * @test
     * @return void
     */
    public function postFromErrorValidationTest()
    {
        Bus::fake();

        $email = $this->getEmail();
        unset($email['from']);
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "from"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);
    }

    /**
     * @test
     * @return void
     */
    public function sendPostSuccessSaveTest()
    {
        Bus::fake();
        Storage::fake('local');

        $response = $this->json('POST', '/api/v1/emails', $this->getEmail());
        $this->assertEquals(200, $response->status());
        $email = $this->getEmail();

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });
    }

    /**
     * @test
     * @return void
     */
    public function ccValidationTest()
    {
        Bus::fake();
        Storage::fake('local');

        $email = $this->getEmail();
        $email['cc'] = "ccOne@email.it";
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "cc"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        $email['cc'] = 123;
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "cc"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        $email['cc'] = [];
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());
        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });

        unset($email['cc']);
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === [] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });
    }


    /**
     * @test
     * @return void
     */
    public function bccValidationTest()
    {
        Bus::fake();
        Storage::fake('local');

        $email = $this->getEmail();
        $email['bcc'] = "bccOne@email.it";
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "bcc"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        $email['bcc'] = 123;
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "bcc"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        $email['bcc'] = [123];
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "bcc.0"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        $email['bcc'] = [];
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });

        unset($email['bcc']);
        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === [] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });
    }


    /**
     * @test
     * @return void
     */
    public function sizeFileValidationTest()
    {
        Bus::fake();
        Storage::fake('local');
        // Array attachments
        $email = $this->getEmail();
        $email['attachments'] = UploadedFile::fake()->create('test.jpg', '10600');

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "attachments"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        // File too big
        $email = $this->getEmail();
        $email['attachments'] = [UploadedFile::fake()->create('test.jpg', '30600')];

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "attachments.0"
            ]
        ]);
        Bus::assertNotDispatched(EmailSender::class);

        // File has to be an attachment
        $email = $this->getEmail();
        $email['attachments'] = ['test'];

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "attachments.0"
            ]
        ]);

        Bus::assertNotDispatched(EmailSender::class);

        // More files too big
        $email = $this->getEmail();
        $email['attachments'] = [UploadedFile::fake()->create('test.jpg', '15600'), UploadedFile::fake()->create('test.jpg', '15600')];

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(422, $response->status());
        $response->assertJsonStructure([
            "errors" => [
                "attachments"
            ]
        ]);
        Bus::assertNotDispatched(EmailSender::class);
    }

    /**
     * @test
     * @return void
     */
    public function sizeFileSuccessTest()
    {
        Bus::fake();
        Storage::fake('local');
        $email = $this->getEmail();
        $email['attachments'] = [UploadedFile::fake()->create('test.jpg', '15600')];

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });

        $email = $this->getEmail();
        $email['attachments'] = [UploadedFile::fake()->create('test.jpg', '0')];

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());
        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });

        $email = $this->getEmail();
        $email['attachments'] = [UploadedFile::fake()->create('test.jpg', '10600'), UploadedFile::fake()->create('test.jpg', '10600')];

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });

        $email = $this->getEmail();
        unset($email['attachments']);

        $response = $this->json('POST', '/api/v1/emails', $email);
        $this->assertEquals(200, $response->status());

        Bus::assertDispatched(EmailSender::class, function (EmailSender $job) use ($email) {
            $emailInfo = $job->getMailable()->getEmail();
            return $emailInfo->getTo() === $email['to'] &&
                $emailInfo->getCc() === $email['cc'] &&
                $emailInfo->getBcc() === $email['bcc'] &&
                $emailInfo->getFrom() === $email['from'] &&
                $emailInfo->getSubject() === $email['subject'] &&
                $emailInfo->getBody() === $email['body'];
        });
    }
}
