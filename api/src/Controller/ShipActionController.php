<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Entity\Game;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

abstract class ShipActionController
{
    public function checkPermissions(Security $security, Game $game): void
    {
        $user = $security->getUser();
        if (!$user) {
            throw new AccessDeniedException("Not logged in.");
        }

        $gameOwner = $game->getOwner();
        if (!$gameOwner) {
            throw new ValidationException('Each game must have an owner.');
        }
        if ($user->getUsername() !== $gameOwner->getUsername()) {
            throw new AccessDeniedException("This is not your game.");
        }
    }
}
