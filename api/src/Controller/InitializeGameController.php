<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class InitializeGameController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Game $data)
    {
        /** @var UserRepository $userRepository */
        $simulatedPlayer = $this->userRepository->findOneBy(['simulated_player' => true]);
        if (!$simulatedPlayer) {
            $simulatedPlayer = (new User())
                ->setSimulatedPlayer(true)
                ->setName('Computer')
                ->setSurname('Localhost')
                ->setEmail('computer@localhost')
                ->setPassword('no-login-possible');

            $this->entityManager->persist($simulatedPlayer);
        }
        if ($data->getSeed()) {
            mt_srand($data->getSeed());
        }

        // Jedes Spiel besteht aus 2 Game-Records: eines je Spieler
        $enemyGame = (new Game())
            ->setOwner($simulatedPlayer)
            ->setEnemyGame($data);
        $this->randomlyPlaceShips($enemyGame);
        $data->setEnemyGame($enemyGame);

        $this->entityManager->persist($data);
        $this->entityManager->persist($enemyGame);

        $this->randomlyPlaceShips($data);

        return $data;
    }

    /**
     * Gibt eine Schiffsposition zurück
     * @param int $shipLength
     *   Die Länge des zu platzierenden Schiffes
     * @return string[]
     */
    private function getRandomPositions(int $shipLength): array
    {
        $horizontal = mt_rand(1, 2) === 1;
        if ($horizontal) {
            $row = chr(mt_rand(ord('A'), ord('J')));
            $col = mt_rand(1, 8 - $shipLength);
            return array_map(static function ($i) use ($row, $col) {
                return $row . ($col + $i);
            }, range(0, $shipLength - 1));
        }

        $row = mt_rand(ord('A'), ord('J') - $shipLength);
        $col = mt_rand(1, 8);
        return array_map(static function ($i) use ($row, $col) {
            return chr($row + $i) . $col;
        }, range(0, $shipLength - 1));

    }

    private function randomlyPlaceShips(Game $game)
    {
        $remainingShipsToPlace = [
            'carrier' => 5, 'battleship' => 4, 'cruiser' => 3, 'submarine' => 3, 'destroyer' => 2
        ];
        $placedShips = [];
        while ($remainingShipsToPlace) {
            $newPositions = $this->getRandomPositions(current($remainingShipsToPlace));
            foreach ($placedShips as $takenPositions) {
                if (array_intersect($takenPositions, $newPositions)) {
                    // Nicht mehr frei
                    continue 2;
                }
            }
            $name = key($remainingShipsToPlace);
            unset($remainingShipsToPlace[$name]);
            $placedShips[$name] = $newPositions;
        }
        $game->setShips($placedShips);
    }
}
