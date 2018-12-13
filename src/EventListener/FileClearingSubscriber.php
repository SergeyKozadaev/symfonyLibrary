<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Filesystem\Filesystem;

class FileClearingSubscriber implements EventSubscriber
{
    private $imageDir;
    private $filesDir;

    public function __construct($imageDir, $filesDir)
    {
        $this->imageDir = $imageDir;
        $this->filesDir = $filesDir;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->clear($args);
    }

    public function clear(LifecycleEventArgs $args)
    {
        $filesystem = new Filesystem();
        $entity = $args->getObject();

        if($entity instanceof Book) {

            $fileSrc = $this->filesDir . $entity->getFile();
            if($entity->getFile() && $filesystem->exists($fileSrc)) {
                $filesystem->remove($fileSrc);
            }

            $imageSrc = $this->imageDir . $entity->getCoverImage();
            if($entity->getCoverImage() && $filesystem->exists($imageSrc)) {
                $filesystem->remove($imageSrc);
            }
        }
    }

}