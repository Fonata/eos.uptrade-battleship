<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\InitializeGameController;
use App\Controller\ShootingController;
use App\Repository\GameRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 * @ApiResource(
 *      routePrefix="/api",
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "security_message"="Only admins can list all games.
 *                                  Use the /api/user/{id} endpoint to get your own games."
 *
 *          },
 *          "post"={
 *              "controller"=InitializeGameController::class,
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_ADMIN') or object.getOwner().getId() == user.getId()"
 *          },
 *          "put"={
 *              "security"="false"
 *          },
 *          "shoot"={
 *              "method"="POST",
 *              "path"="/game/{id}/shoot",
 *              "controller"=ShootingController::class,
 *          },
 *          "patch"={
 *              "security"="false"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_ADMIN') or object.getOwner().getId() == user.getId()"
 *          },
 *      }
 * )
 */
class Game implements TimestampableEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=110)
     * @ApiProperty(
     *     writable=false
     * )
     *
     * Das bedeuten die Zeichen:
     *   - ".": noch kein Schuss
     *   - "H": Red peg:   Hit, not yet sunken
     *   - "S": Red peg:   Sunken
     *   - "M": White peg: Miss
     */
    private $ocean =
        "..........\n" . // A
        "..........\n" . // B
        "..........\n" . // C
        "..........\n" . // D
        "..........\n" . // E
        "..........\n" . // F
        "..........\n" . // G
        "..........\n" . // H
        "..........\n" . // I
        "..........\n"; // J

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "example"="/api/user/1",
     *         }
     *     }
     * )
     */
    private $owner;

    /**
     * @ORM\Column(type="datetime")
     * @ApiProperty(
     *     writable=false
     * )
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     * @ApiProperty(
     *     writable=false
     * )
     */
    private $changed;

    /**
     * @ORM\Column(type="json")
     * @ApiProperty(
     *     writable=false
     * )
     */
    private $ships = [];

    /**
     * @ORM\OneToOne(targetEntity=Game::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(
     *     writable=false
     * )
     */
    private $enemy_game;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @ApiProperty(
     *     writable=false
     * )
     *
     * // Lowercase ship names.
     */
    private $sunken_ships = [];

    /**
     * @ApiProperty(
     *     writable=false
     * )
     *
     * For example "submarine".
     */
    public string $last_sunken_ship = '';

    /**
     * "Hit. _SOME-SHIP_." or "Miss."
     *
     * @ApiProperty(
     *     writable=false
     * )
     */
    public string $last_shot_result = '';

    /**
     * @ApiProperty(
     *     writable=false,
     * )
     */
    public string $last_shot_target = '';
    private $peg_counts;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $winner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOcean(): ?string
    {
        return $this->ocean;
    }

    public function setOcean(string $ocean): self
    {
        $this->ocean = $ocean;
        if (strlen($ocean) !== 110) {
            throw new BadRequestException("An ocean must have 110 characters.");
        }
        if (substr_count($ocean, "\n") !== 10) {
            throw new BadRequestException("An ocean must have 10 rows.");
        }
        $this->peg_counts = count_chars($ocean, 1);
        ksort($this->peg_counts);
        $allowedChars = ['.', 'M', 'H', 'S', "\n"];
        $invalidChars = array_diff(array_keys($this->peg_counts), array_map('ord', $allowedChars));
        if ($invalidChars) {
            throw new BadRequestException(sprintf(
                "%s unexpected.
                An ocean can only contain the following characters: %s.",
                implode(', ', $invalidChars),
                $allowedChars));
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        if (!$owner && $this->owner) {
            throw new BadRequestException('You cannot clear the owner once it is set.');
        }

        $this->owner = $owner;

        return $this;
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getChanged(): ?DateTimeInterface
    {
        return $this->changed;
    }

    public function setChanged(DateTimeInterface $changed): self
    {
        $this->changed = $changed;

        return $this;
    }

    /** @return array{
     *      carrier: string[],
     *      battleship: string[],
     *      cruiser: string[],
     *      submarine: string[],
     *      destroyer: string[]}
     *
     *  Die Listen enthalten jeweils Koordinaten, z.B. ["A1", "A2", "A3"]
     */
    public function getShips(): array
    {
        return $this->ships;
    }

    /**
     * @param array{carrier: string[], battleship: string[], cruiser: string[], submarine: string[], destroyer: string[]} $ships
     * @return $this
     */
    public function setShips(array $ships): self
    {
        array_map(self::class . '::validateShipName', array_keys($ships));
        foreach ($ships as $positions) {
            array_map(self::class . '::validateTarget', $positions);
        }
        $this->ships = $ships;

        return $this;
    }

    public function getEnemyGame(): ?self
    {
        return $this->enemy_game;
    }

    public function setEnemyGame(self $enemy_game): self
    {
        if (!$enemy_game && $this->enemy_game) {
            throw new BadRequestException('You cannot clear the game once it is set.');
        }
        $this->enemy_game = $enemy_game;

        return $this;
    }

    public function getSunkenShips(): ?array
    {
        return $this->sunken_ships;
    }

    public function setSunkenShips(array $sunken_ships): self
    {
        array_map(self::class . '::validateShipName', $sunken_ships);
        if (array_diff(array_count_values($sunken_ships), [1])) {
            throw new BadRequestException('Each ship can only be sunken once.');
        }

        $enemyGame = $this->getEnemyGame();
        if ($enemyGame && count($sunken_ships) === 5) {
            $this->setWinner('Enemy');
            $enemyGame->setWinner('Player');
        }

        $this->sunken_ships = $sunken_ships;

        return $this;
    }

    public static function validateTarget(string $target)
    {
        if (!preg_match('/^[A-J][1-9]0*$/', $target)) {
            throw new BadRequestException("Target should be a letter and a number.");
        }

        $col = substr($target, 1);
        if ($col < 1 || $col > 10) {
            throw new BadRequestException("Target should be a letter and a number in the range from 1 to 10 .");
        }
    }

    public static function validateShipName(string $ship)
    {
        $valid = in_array($ship, ["carrier", "battleship", "cruiser", "submarine", "destroyer"], true);
        if (!$valid) {
            throw new BadRequestException('Invalid ship: ' . $ship);
        }
    }

    /**
     * @param string $ship
     *   "carrier", "battleship" ,"cruiser", "submarine" or "destroyer".
     */
    public function markShipAsSunken(string $ship)
    {
        self::validateShipName($ship);
        $this->last_sunken_ship = $ship;

        $sunkenShips = $this->getSunkenShips();
        $sunkenShips[] = $ship;
        $this->setSunkenShips($sunkenShips);

        foreach ($this->ships[$ship] as $position) {
            $this->ocean[self::coordinatesToCharInOcean($position)] = 'S';
        }
    }

    public static function coordinatesToCharInOcean(string $target)
    {
        $col = substr($target, 1);
        return (ord($target[0]) - 65) * 11 + $col - 1;
    }

    public function getWinner(): ?string
    {
        return $this->winner;
    }

    public function setWinner(?string $winner): self
    {
        $valid = in_array($winner, ["Player", "Enemy"], true);
        if (!$valid) {
            throw new BadRequestException('Invalid winner: ' . $winner);
        }
        $this->winner = $winner;

        return $this;
    }
}
