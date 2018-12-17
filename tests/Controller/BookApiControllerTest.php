<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookApiControllerTest extends WebTestCase
{
    const TEST_API_KEY = "some_secret_key";

    public function testBookAdd()
    {
        $client = $this->createClient();

        $client->xmlHttpRequest(
            'POST',
            '/api/v1/books/add',
            [
                "key" => self::TEST_API_KEY,
                "title" => "Название книги API TEST",
                "author" => "Автор книги API TEST",
                "addedDate" => "no proper date"
            ]
        );

        $response = $client->getResponse();

        $this->assertTrue($response->isSuccessful());

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $this->assertContains('"status":"ok"', $response->getContent());
    }
}