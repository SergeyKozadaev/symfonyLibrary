<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Filesystem\Filesystem;

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
            Events::preRemove
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->clear($entity);
    }

    public function clear($entity)
    {
        $filesystem = new Filesystem();

        if($entity instanceof Book) {

            $fileSrc = $this->publicDir . $this->filesDir . $entity->getFile();
            if($entity->getFile() && $filesystem->exists($fileSrc)) {
                $filesystem->remove($fileSrc);
            }

            $imageSrc = $this->publicDir . $this->imagesDir . $entity->getCoverImage();
            if($entity->getCoverImage() && $filesystem->exists($imageSrc)) {
                $filesystem->remove($imageSrc);
            }
        }
    }

}