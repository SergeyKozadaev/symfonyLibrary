<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;

class AppBasicTest extends WebTestCase
{
    protected $client;

    protected $image;
    protected $file;

    protected $userStr; // login, firstName & password for testUser; bookAuthor & bookTitle
    protected $userEmail;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->image = $kernel->getContainer()->getParameter('test.image');
        $this->file = $kernel->getContainer()->getParameter('test.file');

        $str = 'TestUser'.random_int(1000000, 9999999);
        $this->userStr = $str;
        $this->userEmail = $str.'@test.test';
    }

    protected function checkUserRegistration()
    {
        $crawler = $this->client->request('GET', '/register/');

        $registerForm = $crawler
            ->selectButton('_submit')
            ->form([
                'fos_user_registration_form[email]' => $this->userEmail,
                'fos_user_registration_form[username]' => $this->userStr,
                'fos_user_registration_form[firstName]' => $this->userStr,
                'fos_user_registration_form[plainPassword][first]' => $this->userStr,
                'fos_user_registration_form[plainPassword][second]' => $this->userStr,
            ]);

        $this->client->submit($registerForm);

        $this->assertTrue($this->client->getResponse()->isRedirect('/register/confirmed'));
    }

    protected function checkUserAuthorisation()
    {
        $this->client->request('GET', '/profile/');

        $this->assertFalse($this->client->getResponse()->isRedirect('/login'));
    }

    protected function checkBookAddFormExistence()
    {
        $crawler = $this->client->request('GET', '/books/add');

        $form = $crawler->filterXPath('//form[@name="book_add_form"]');
        $this->assertTrue(1 === $form->count());
    }

    protected function checkBookAdd()
    {
        $crawler = $this->client->request('GET', '/books/add');

        $bookAddForm = $crawler
            ->selectButton('_submit')
            ->form([
                'book_add_form[title]' => $this->userStr,
                'book_add_form[author]' => $this->userStr,
                'book_add_form[coverImage]' => new File($this->image),
                'book_add_form[file]' => new File($this->file),
                'book_add_form[downloadable]' => true,
            ]);

        $this->client->submit($bookAddForm);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('h3:contains('.$this->userStr.')')->count());
    }
}
