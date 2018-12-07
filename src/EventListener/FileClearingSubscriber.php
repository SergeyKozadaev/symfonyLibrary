<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Filesystem\Filesystem;

class FileClearingSubscriber implements EventSubscriber
{
    const IMAGE_DIR = 'upload/images/';
    const FILE_DIR = 'upload/files/';

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

            if($entity->getFile() && $filesystem->exists(self::FILE_DIR . $entity->getFile())) {
                $filesystem->remove(self::FILE_DIR . $entity->getFile());
            }

            if($entity->getCoverImage() && $filesystem->exists(self::IMAGE_DIR . $entity->getCoverImage())) {
                $filesystem->remove(self::IMAGE_DIR . $entity->getCoverImage());
            }
        }
    }

}