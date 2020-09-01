<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *      routePrefix="/api",
 *      iri="http://schema.org/Person",
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "security_message"="Only admins can list all users."
 *          },
 *          "post"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "security_message"="Only admins can create new users."
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_ADMIN') or object.getId() == user.getId()"
 *          },
 *          "put"={
 *              "security"="is_granted('ROLE_ADMIN')"
 *          },
 *          "patch"={
 *              "security"="is_granted('ROLE_ADMIN')"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_ADMIN') or object.getId() == user.getId()",
 *              "swagger_context"={
 *                  "summary" = "Change user password"
 *              }
 *          }
 *      }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email")
 */
class User implements UserInterface, TimestampableEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @ApiProperty(
     *     iri="http://schema.org/givenName",
     *     attributes={
     *         "openapi_context"={
     *             "example"="Christian",
     *         }
     *     }
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiProperty(
     *     iri="http://schema.org/familyName",
     *     attributes={
     *         "openapi_context"={
     *             "example"="Bläul",
     *         }
     *     }
     * )
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "example"="christian@blaeul.de",
     *         }
     *     }
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @ApiProperty(
     *     iri="http://schema.org/email",
     *     attributes={
     *         "openapi_context"={
     *             "type"="array",
     *             "items"={"type"="string", "example"="ROLE_USER", "enum"={"ROLE_USER", "ROLE_ADMIN"}}
     *         }
     *     }
     * )
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @ApiProperty(
     *     readable=false
     * )
     */
    private $password;

    /**
     * @var string|null The unhashed password - don't persist!
     * @ApiProperty(
     *     readable=false,
     *     writable=false
     * )
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity=Game::class, mappedBy="owner", orphanRemoval=true)
     * @ApiProperty(
     *     writable=false
     * )
     */
    private $games;

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
     * @ORM\Column(type="boolean")
     */
    private $simulated_player = false;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     * @ApiProperty(
     *     readable=false,
     *     writable=false
     * )
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * @return string
     * @see UserInterface
     */
    public function getPassword(): string
    {
        /** @noinspection UnnecessaryCastingInspection */
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        if (!str_starts_with($password, '$argon2id$')) {
            $this->plainPassword = $password;
        }
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     * @ApiProperty(
     *     readable=false,
     *     writable=false
     * )
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setOwner($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
            // set the owning side to null (unless already changed)
            if ($game->getOwner() === $this) {
                $game->setOwner(null);
            }
        }

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

    public function getSimulatedPlayer(): ?bool
    {
        return $this->simulated_player;
    }

    public function setSimulatedPlayer(bool $simulated_player): self
    {
        $this->simulated_player = $simulated_player;

        return $this;
    }
}
