<?php

namespace App\EventListener;

use App\Controller\InitializeGameController;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * Handle security and default field values before changing the database.
 */
class GameEventListener implements EventSubscriber
{
    /**
     * @var Security
     */
    private Security $security;
    /**
     * @var InitializeGameController
     */
    private InitializeGameController $initializeGameController;

    public function __construct(Security $security, InitializeGameController $initializeGameController)
    {
        $this->security = $security;
        $this->initializeGameController = $initializeGameController;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
        ];
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->handleOwner($event);
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $this->handleOwner($event);
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        $this->handleOwner($event);
    }

    private function handleOwner(LifecycleEventArgs $event): void
    {
        if (!$event->getObject() instanceof Game) {
            return;
        }

        $loggedInUser = $this->security->getUser();
        if (!$loggedInUser instanceof User) {
            throw new AccessDeniedException();
        }

        /** @var Game $Game */
        $Game = $event->getObject();

        if (!$Game->getOwner()) {
            $Game->setOwner($loggedInUser);
        }

        $roles = $loggedInUser->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            // Admins can do what they want.
            return;
        }

        if ($Game->getOwner()->getEmail() !== $loggedInUser->getEmail()) {
            throw new AccessDeniedException("You cannot edit other users' Games.");
        }
    }
}
