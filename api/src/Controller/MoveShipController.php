<?php

namespace App\Controller;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Nur am Anfang des Spiels erlaubt: Spieler verschiebt seine Schiffe
 */
class MoveShipController extends ShipActionController
{
    public function __invoke(Request $request, Security $security, EntityManagerInterface $entityManager, Game $data)
    {
        $this->checkPermissions($security, $data);

        $placedShips = $this->validateRequest($request, $data);
        $data->setShips($placedShips);
        $entityManager->persist($data);

        return $data;
    }

    /**
     * @param Request $request
     * @param Game $game
     * @return array
     *   Positionen im Format von Game::getShips()
     *
     * @throws JsonException
     */
    public function validateRequest(Request $request, Game $game): array
    {
        if (str_replace(["\n", "."], '', $game->getOcean())) {
            throw new BadRequestException('You cannot move ships after the game has started.');
        }
        $postedData = json_decode($request->getContent(), true, 3, JSON_THROW_ON_ERROR);
        if (count($postedData) !== 1) {
            throw new BadRequestException('You must give one new ship position at a time.');
        }
        $shipToMove = key($postedData);
        $expectedLength = Game::getShipLengths()[$shipToMove] ?? 0;
        if (!$expectedLength) {
            throw new BadRequestException($shipToMove . ' is not a valid ship name.');
        }
        $newPositions = current($postedData);
        if (count($newPositions) !== $expectedLength) {
            throw new BadRequestException($shipToMove . sprintf(' should have length %d, but %d positions were POSTed.',
                    $expectedLength,
                    count($newPositions)));
        }
        array_map(Game::class . '::validateTarget', $newPositions);
        $first_letter = substr($newPositions[0], 0, 1);
        $first_col = substr($newPositions[0], 1);
        $horizontal = str_starts_with($newPositions[1], $first_letter);
        if ($horizontal) {
            $newPositions = array_map(static function ($number) use ($first_letter, $first_col) {
                return $first_letter . ($first_col + $number);
            }, range(0, $expectedLength - 1));
        } else {
            $newPositions = array_map(static function ($number) use ($first_letter, $first_col) {
                return chr(ord($first_letter) + $number) . $first_col;
            }, range(0, $expectedLength - 1));
        }

        $placedShips = $game->getShips();
        unset($placedShips[$shipToMove]);
        foreach ($placedShips as $takenPositions) {
            $overlap = array_intersect($takenPositions, $newPositions);
            if ($overlap) {
                throw new BadRequestException(sprintf("The position %s is taken.", implode(', ', $overlap)));
            }
        }
        $placedShips[$shipToMove] = $newPositions;
        return $placedShips;
    }
}
