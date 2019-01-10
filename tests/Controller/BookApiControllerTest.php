<?php

namespace App\Tests\Controller;

use App\Tests\AppBasicTest;

class BookApiControllerTest extends AppBasicTest
{
    private $apiKey;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $kernel = self::bootKernel();
        $this->apiKey = $kernel->getContainer()->getParameter('app.api_key');

        $this->userStr = 'Api'.$this->userStr;
    }

    public function testBookAdd()
    {
        $this->checkBookAdd();
    }

    protected function checkBookAdd()
    {
        $client = $this->createClient();

        $client->xmlHttpRequest(
            'POST',
            '/api/v1/books/add',
            [
                'key' => $this->apiKey,
                'title' => $this->userStr,
                'author' => $this->userStr,
                'addedDate' => '',
            ]
        );

        $response = $client->getResponse();

        $this->assertTrue($response->isSuccessful());

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $this->assertContains('"status":"ok"', $response->getContent());
    }
}
