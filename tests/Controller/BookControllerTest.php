<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    //private $client;
    //private $entityManager;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        //$this->client = static::createClient();
        $kernel = self::bootKernel();
        //$this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }



    public function testUserLogin()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler
            ->selectButton('_submit')
            ->form([
                '_username' => 'test1',
                '_password' => 'test1'
            ])
        ;

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        //dump($crawler);
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Выйти")')->count());
    }

/*
    public function testShowList()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/book/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
*/
}