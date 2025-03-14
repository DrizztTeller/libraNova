<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $rented_novels_count = null;

    #[ORM\Column]
    private bool $is_adult = false;

    #[ORM\Column(length: 255)]
    private ?string $ref = null;

    /**
     * @var Collection<int, Novel>
     */
    #[ORM\ManyToMany(targetEntity: Novel::class, inversedBy: 'likes')]
    private Collection $novels;

    /**
     * @var Collection<int, RentingHistory>
     */
    #[ORM\OneToMany(targetEntity: RentingHistory::class, mappedBy: 'user')]
    private Collection $rentings;

    /**
     * @var Collection<int, LoginHistory>
     */
    #[ORM\OneToMany(targetEntity: LoginHistory::class, mappedBy: 'user')]
    private Collection $loginHistories;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private bool $is_terms = false;

    #[ORM\Column]
    private bool $is_gpdr = false;

    public function __construct()
    {
        $this->novels = new ArrayCollection();
        $this->rentings = new ArrayCollection();
        $this->loginHistories = new ArrayCollection();
        $this->rented_novels_count = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getRentedNovelsCount(): ?int
    {
        return $this->rented_novels_count;
    }

    public function setRentedNovelsCount(int $rented_novels_count): static
    {
        $this->rented_novels_count = $rented_novels_count;

        return $this;
    }

    public function isAdult(): ?bool
    {
        return $this->is_adult;
    }

    public function setIsAdult(bool $is_adult): static
    {
        $this->is_adult = $is_adult;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * @return Collection<int, Novel>
     */
    public function getNovels(): Collection
    {
        return $this->novels;
    }

    public function addNovel(Novel $novel): static
    {
        if (!$this->novels->contains($novel)) {
            $this->novels->add($novel);
        }

        return $this;
    }

    public function removeNovel(Novel $novel): static
    {
        $this->novels->removeElement($novel);

        return $this;
    }

    /**
     * @return Collection<int, RentingHistory>
     */
    public function getRentings(): Collection
    {
        return $this->rentings;
    }

    public function addRenting(RentingHistory $renting): static
    {
        if (!$this->rentings->contains($renting)) {
            $this->rentings->add($renting);
            $renting->setUser($this);
        }

        return $this;
    }

    public function removeRenting(RentingHistory $renting): static
    {
        if ($this->rentings->removeElement($renting)) {
            // set the owning side to null (unless already changed)
            if ($renting->getUser() === $this) {
                $renting->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LoginHistory>
     */
    public function getLoginHistories(): Collection
    {
        return $this->loginHistories;
    }

    public function addLoginHistory(LoginHistory $loginHistory): static
    {
        if (!$this->loginHistories->contains($loginHistory)) {
            $this->loginHistories->add($loginHistory);
            $loginHistory->setUser($this);
        }

        return $this;
    }

    public function removeLoginHistory(LoginHistory $loginHistory): static
    {
        if ($this->loginHistories->removeElement($loginHistory)) {
            // set the owning side to null (unless already changed)
            if ($loginHistory->getUser() === $this) {
                $loginHistory->setUser(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isTerms(): ?bool
    {
        return $this->is_terms;
    }

    public function setIsTerms(bool $is_terms): static
    {
        $this->is_terms = $is_terms;

        return $this;
    }

    public function isGpdr(): ?bool
    {
        return $this->is_gpdr;
    }

    public function setIsGpdr(bool $is_gpdr): static
    {
        $this->is_gpdr = $is_gpdr;

        return $this;
    }
}
