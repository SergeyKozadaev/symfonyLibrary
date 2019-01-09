<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileClearingSubscriber implements EventSubscriber
{
    private $imagesDir;
    private $filesDir;
    private $publicDir;

    public function __construct($imagesDir, $filesDir, $publicDir)
    {
        $this->imagesDir = $imagesDir;
        $this->filesDir = $filesDir;
        $this->publicDir = $publicDir;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->clear($entity);
    }

    public function fileRemove(string $file)
    {
        $filesystem = new Filesystem();
        $finder = new Finder();

        $fileSrc = $this->publicDir.$file;

        if ($filesystem->exists($fileSrc)) {
            $filesystem->remove($fileSrc);

            $dir = mb_substr($fileSrc, 0, strrpos($fileSrc, '/'));
            $arFiles = $finder->files()->in($dir);

            if (0 === count($arFiles)) {
                $filesystem->remove($dir);
            }
        }
    }

    public function clear($entity)
    {
        if (!$entity instanceof Book) {
            return;
        }

        if (null !== $entity->getFile()) {
            $this->fileRemove($this->filesDir.$entity->getFile());
        }

        if (null !== $entity->getCoverImage()) {
            $this->fileRemove($this->imagesDir.$entity->getCoverImage());
        }
    }
}
