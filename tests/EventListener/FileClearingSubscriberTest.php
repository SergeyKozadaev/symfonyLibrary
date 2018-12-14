<?php

namespace App\Tests\EventListener;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class FileClearingSubscriberTest extends WebTestCase
{
    const BOOK_ID = 236;
    private $entityManager;
    private $filesDir;
    private $imagesDir;
    private $publicDir;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->filesDir = $kernel->getContainer()->getParameter('files_directory');
        $this->imagesDir = $kernel->getContainer()->getParameter('images_directory');
        $this->publicDir = $kernel->getContainer()->getParameter('public_directory');

    }

    public function testClear()
    {
        $repository = $this->entityManager->getRepository(Book::class);
        $book = $repository->findOneBy(['id' => self::BOOK_ID]);

        $this->assertTrue($book !== null);

        $file = $book->getFile();
        $image = $book->getCoverImage();

        // иначе detached entity cannot be removed
        //$bookManaged = $this->entityManager->merge($book);
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        $fileSystem = new Filesystem();

        if($file !== null) {
            dump("public/" . $this->filesDir . $file);
            $this->assertTrue(!$fileSystem->exists("public/" . $this->filesDir . $file));
        }

        if($image !== null) {
            dump("public/" . $this->imagesDir . $image);
            $this->assertTrue(!$fileSystem->exists("public/" . $this->imagesDir . $image));
        }

        $book = $repository->findOneBy(['id' => self::BOOK_ID]);

        $this->assertTrue($book === null);
    }

}