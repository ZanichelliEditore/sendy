<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EmailWithAuthTest extends TestCase
{

    /**
        * Create auth header from an access token
        * @param string $access_token
        * @return  array
        */
    private function createHeader($access_token)
    {
        return [
            'Accept' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $access_token
        ];
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
            'attachments' => UploadedFile::fake()->image('photo1.jpg'),
        ];
    }

    public function testFailAuthentication()
    {
        $response = $this->post('/api/v1/emails', $this->getEmail(), $this->createHeader('TestError'));
        $this->assertEquals(401, $response->status());
    }

}
