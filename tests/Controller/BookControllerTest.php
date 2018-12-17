<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    const USERNAME = "test1";
    const PASSWORD = "test1";

    public function testBookAdd()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/login');

        $loginForm = $crawler
            ->selectButton('_submit')
            ->form([
                '_username' => self::USERNAME,
                '_password' => self::PASSWORD
            ])
        ;

        $client->submit($loginForm);
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $link = $crawler->filter('a:contains("Добавить книгу")');
        $this->assertGreaterThan(0, $link->count());

        $crawler = $client->click($link->link());
        $this->assertGreaterThan(0, $crawler->filter('h2:contains("Добавим новую книгу!")')->count());

        $bookAddForm = $crawler
            ->selectButton('_submit')
            ->form([
                'book_add_form[title]' => 'Название книги ТЕСТ',
                'book_add_form[author]' => 'Автор книги ТЕСТ'
            ])
        ;

        $client->submit($bookAddForm);
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('h3:contains("Название книги ТЕСТ")')->count());
    }
}