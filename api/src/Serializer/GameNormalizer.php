<?php

namespace App\Serializer;

use App\Entity\Game;
use App\Security\UserPermissionChecker;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * This class adds the enemy's ocean to the JSON output.
 */
class GameNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    /**
     * @var Security
     */
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'GAME_NORMALIZER_ALREADY_CALLED';

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Game;
    }

    /**
     * @param Game $object
     * @param string|null $format
     * @param array $context
     * @return mixed
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $array = $this->normalizer->normalize($object, $format, $context);
        if (!count($array)) {
            return $array;
        }
        $loggedInUser = $this->security->getUser();
        $gameOwner = $object->getOwner();
        if (!$loggedInUser || !$gameOwner || $gameOwner->getUsername() !== $loggedInUser->getUsername()) {
            // fremde Schiffe verstecken
            unset($array['ships']);
        }

        $enemyGame = $object->getEnemyGame();
        if ($enemyGame) {
            $array['target_ocean'] = $enemyGame->getOcean();
            $array['sunken_ships'] = $enemyGame->getSunkenShips();
        }

        return $array;
    }
}
