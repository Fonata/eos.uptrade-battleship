<?php

namespace App\EventListener;

use App\Controller\InitializeGameController;
use App\Entity\Game;
use App\Entity\TimestampableEntityInterface;
use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * Set created and changed timestamps for a little idea on the lifecycle of objects.
 */
class TimestampableEntityListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($object instanceof TimestampableEntityInterface){
            $object->setChanged(new \DateTime());
        }
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($object instanceof TimestampableEntityInterface) {
            $object->setCreated(new \DateTime());
            $object->setChanged(new \DateTime());
        }
    }
}
