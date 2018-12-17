<?php

namespace App\Tests\EventListener;

use App\Entity\Book;
use App\EventListener\FileClearingSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class FileClearingSubscriberTest extends WebTestCase
{
    const BOOK_ID = 243;
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

        $this->assertTrue($book !== null, "Нет книги с id = " . self::BOOK_ID);

        $file = $book->getFile();
        $image = $book->getCoverImage();

        $fileClearingHandler = new FileClearingSubscriber($this->imagesDir, $this->filesDir, $this->publicDir);
        $fileClearingHandler->clear($book);

        $fileSystem = new Filesystem();

        if($file !== null) {
            $fileSrc = $this->publicDir . $this->filesDir . $file;
            $this->assertTrue(!$fileSystem->exists($fileSrc), "Файл книги не удален, путь: " . $fileSrc);
        }

        if($image !== null) {
            $imageSrc = $this->publicDir . $this->imagesDir . $image;
            $this->assertTrue(!$fileSystem->exists($imageSrc), "Обложка книги не удалена, путь: " . $fileSrc);
        }
    }

}