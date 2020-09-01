<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * This class makes sure that the database contains only hashed passwords.
 */
class UserEventListener implements EventSubscriber
{
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->updatePasswordHash($event);
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $this->updatePasswordHash($event);
    }

    private function updatePasswordHash(LifecycleEventArgs $event): void
    {
        if (!$event->getObject() instanceof User) {
            return;
        }

        /** @var User $user */
        $user = $event->getObject();
        if ($user->getPlainPassword() === null) {
            // Password was not changed, no need to rehash.
            return;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setPlainPassword(null);
    }
}
