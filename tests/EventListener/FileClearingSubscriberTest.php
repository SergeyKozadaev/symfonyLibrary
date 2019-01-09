<?php

namespace App\Tests\EventListener;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Tests\AppBasicTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileClearingSubscriberTest extends AppBasicTest
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    private $filesDir;
    private $imagesDir;
    private $publicDir;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $this->filesDir = $kernel->getContainer()->getParameter('app.files_directory');
        $this->imagesDir = $kernel->getContainer()->getParameter('app.images_directory');
        $this->publicDir = $kernel->getContainer()->getParameter('app.public_directory');

        $this->userStr = 'Clear'.$this->userStr;
    }

    private function getFirstBookId()
    {
        $crawler = $this->client->request('GET', '/');

        $bookElement = $crawler->filter('div.container:contains('.$this->userStr.')');

        return $bookElement->count() > 0 ? $bookElement->attr('id') : null;
    }

    private function checkBookRemove(int $bookId)
    {
        /** @var BookRepository $repository */
        $repository = $this->entityManager->getRepository(Book::class);
        /** @var Book $book */
        $book = $repository->findOneBy(['id' => $bookId]);

        $this->assertTrue(null !== $book);

        $file = $book->getFile();
        $image = $book->getCoverImage();

        $book = $this->entityManager->merge($book);
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        $fileSystem = new Filesystem();

        if (null !== $file) {
            $fileSrc = $this->publicDir.$this->filesDir.$file;
            $this->assertTrue(!$fileSystem->exists($fileSrc), '');
        }

        if (null !== $image) {
            $imageSrc = $this->publicDir.$this->imagesDir.$image;
            $this->assertTrue(!$fileSystem->exists($imageSrc));
        }
    }

    public function testBookRemove()
    {
        $this->checkUserRegistration();

        $this->checkUserAuthorisation();

        $this->checkBookAddFormExistence();

        $this->checkBookAdd();

        $bookId = $this->getFirstBookId();

        $this->checkBookRemove($bookId);
    }
}
