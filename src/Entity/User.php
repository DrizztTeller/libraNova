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
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(
        min: 8,
        minMessage: "Le mot de passe doit contenir au moins 8 caractères."
    )]
    #[Assert\Regex(
        pattern: '/[!@#$%^&*(),.?":{}|<>]/',
        message: "Le mot de passe doit contenir au moins un caractère spécial."
    )]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire.")]
    #[Assert\Length(min: 3, max: 50, minMessage: "Le nom d'utilisateur doit contenir au moins 3 caractères.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\-]+$/',
        message: "Le nom d'utilisateur ne peut contenir que des lettres, des chiffres et des traits d'union."
    )]  
    private ?string $username = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\PositiveOrZero(message: "Le nombre de romans empruntés ne peut pas être négatif.")]
    private ?int $rented_novels_count = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'information sur la majorité est requise.")]
    private ?bool $is_adult = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La référence est obligatoire.")]
    private ?string $ref = null;

    #[ORM\ManyToMany(targetEntity: Novel::class, inversedBy: 'likes')]
    private Collection $novels;

    #[ORM\OneToMany(targetEntity: RentingHistory::class, mappedBy: 'user')]
    private Collection $rentings;

    #[ORM\OneToMany(targetEntity: LoginHistory::class, mappedBy: 'user')]
    private Collection $loginHistories;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'acceptation des conditions générales est requise.")]
    private ?bool $is_terms = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'acceptation de la politique de confidentialité est requise.")]
    private ?bool $is_gpdr = null;

    public function __construct()
    {
        $this->novels = new ArrayCollection();
        $this->rentings = new ArrayCollection();
        $this->loginHistories = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void {}

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
            if ($renting->getUser() === $this) {
                $renting->setUser(null);
            }
        }

        return $this;
    }

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
