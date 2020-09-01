<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Wird bei jedem Spielzug des Clients aufgerufen
 */
class ShootingController extends AbstractController
{
    public function __invoke(Request $request, Security $security, EntityManagerInterface $entityManager, Game $data)
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(
                ['status' => 'error', 'reason' => "Not logged in."],
                JsonResponse::HTTP_FORBIDDEN);
        }

        $gameOwner = $data->getOwner();
        if (!$gameOwner) {
            throw new ValidationException('Each game must have an owner.');
        }
        if ($user->getUsername() !== $gameOwner->getUsername()) {
            return new JsonResponse(
                ['status' => 'error', 'reason' => "This is not your game."],
                JsonResponse::HTTP_FORBIDDEN);

        }

        $this->savePlayerShot($request, $data, $entityManager);
        $this->saveComputerShot($data, $entityManager);
        $entityManager->persist($data);
        $entityManager->persist($data->getEnemyGame());

        return $data;
    }

    private function saveShot(Game $game, string $target, EntityManagerInterface $entityManager)
    {
        $game->last_shot_target = $target;
        $ocean = $game->getOcean();

        $charInOcean = Game::coordinatesToCharInOcean($target);

        if ($ocean[$charInOcean] !== '.') {
            throw new BadRequestException("This target has been shot at already.");
        }

        $result = 'Miss.';
        foreach ($game->getShips() as $name => $positions) {
            $i = array_search($target, $positions, true);
            if ($i !== false) {
                $result = 'Hit. ' . ucfirst($name);
                unset($positions[$i]);
                $sunk = $name;
                foreach ($positions as $position) {
                    if ($ocean[Game::coordinatesToCharInOcean($position)] === '.') {
                        $sunk = false;
                        break;
                    }
                }
                break;
            }
        }
        $ocean[$charInOcean] = $result[0];
        $game->last_shot_result = $result;
        $game->setOcean($ocean);
        if (!empty($sunk)) {
            $game->markShipAsSunken($sunk);
        }
    }

    /**
     * @param Request $request
     * @param Game $data
     * @param EntityManagerInterface $entityManager
     */
    private function savePlayerShot(Request $request, Game $data, EntityManagerInterface $entityManager)
    {
        $postedData = json_decode($request->getContent(), true, 2, JSON_THROW_ON_ERROR);
        $target = $postedData['target'] ?? '';
        Game::validateTarget($target);

        $enemyGame = $data->getEnemyGame();
        if (!$enemyGame) {
            throw new ValidationException('Each game must have an enemy.');
        }
        $this->saveShot($enemyGame, $target, $entityManager);
        $enemyGame->last_sunken_ship;
    }

    private function saveComputerShot(Game $game, EntityManagerInterface $entityManager)
    {
        $ocean = $game->getOcean();
        $charInOcean = $this->findHarmedShip($ocean);
        if ($charInOcean === null) {
            do {
                $charInOcean = 2 * random_int(0, 54);
            } while ($ocean[$charInOcean] !== '.');
        }
        $col = $charInOcean % 11 + 1;
        $target = chr(65 + intdiv($charInOcean, 11)) . $col;
        $this->saveShot($game, $target, $entityManager);
    }

    private function findHarmedShip(string $ocean): ?int
    {
        $i = strpos($ocean, 'H');
        if ($i === false) {
            // kein angeschossenes, aber ungesunkenes Schiff gefunden
            return null;
        }
        while ($ocean[$i + 1] === 'H') {
            // horizontal nach rechts
            $i++;
        }
        if ($ocean[$i + 1] === '.') {
            return $i + 1;
        }

        while ($i > 0 && $ocean[$i - 1] === 'H') {
            // horizontal nach links
            $i--;
        }
        if ($i > 0 && $ocean[$i - 1] === '.') {
            return $i - 1;
        }

        while ($i < 99 && $ocean[$i + 11] === 'H') {
            // horizontal nach rechts
            $i += 11;
        }
        if ($i < 99 && $ocean[$i + 11] === '.') {
            return $i + 11;
        }

        while ($i > 10 && $ocean[$i - 11] === 'H') {
            // horizontal nach links
            $i -= 11;
        }
        if ($i > 10 && $ocean[$i - 11] === '.') {
            return $i - 11;
        }

        return null;
    }
}
