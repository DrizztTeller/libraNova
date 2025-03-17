<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email.')]
#[HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9._%+-]+(?!\..)[A-Za-z0-9._%+-]*@[A-Za-z0-9.-]+(?<!\.\.)\.[A-Za-z]{2,}$/',
        message: "Votre email ne peut contenir que des lettres sans accents, des chiffres, des points, underscores, traits d'union et les symboles % et +"
    )]
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
        pattern: '/^(?=.*[!@#$%^*-])(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])\S{12,}$/',
        message: "Le mot de passe doit être composé d'au moins 12 caractères consécutifs, sans espace, et doit contenir au moins une lettre Majuscule, une lettre minuscule, un chiffre et un caractère spécial parmis ! @ # $ % ^ * -"
    )]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom d'utilisateur doit contenir au moins 2 caractères.",
        maxMessage: "Le nom d'utilisateur ne peut avoir que 50 caractères au maximum."
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_\s\-éèêëàâäîïôöùûüçñÑ&\'"]{2,50}$/',
        message: "Le nom d'utilisateur ne peut contenir que des lettres, des chiffres, des espaces, des traits d'union, des underscores et les apostrophes. Il doit avoir entre 2 et 50 caractères"
    )]
    private ?string $username = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\PositiveOrZero(message: "Le nombre de romans empruntés ne peut pas être négatif.")]
    private int $rented_books_count = 0;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'information sur la majorité est requise.")]
    private bool $is_adult = false;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La référence est obligatoire.")]
    private ?string $ref = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'likes')]
    private Collection $books;

    #[ORM\OneToMany(targetEntity: RentingHistory::class, mappedBy: 'user')]
    private Collection $rentings;

    #[ORM\OneToMany(targetEntity: LoginHistory::class, mappedBy: 'user')]
    private Collection $loginHistories;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'acceptation des conditions générales est requise.")]
    private bool $is_terms = false;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'acceptation de la politique de confidentialité est requise.")]
    private bool $is_gpdr = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->books = new ArrayCollection();
        $this->rentings = new ArrayCollection();
        $this->loginHistories = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable;
    }

    #[ORM\PrePersist]
    public function setUpdatedAtValue()
    {
        $this->updated_at = new \DateTimeImmutable;
    }

    #[ORM\PrePersist]
    public function addDefaultRolesOnCreation(): void
    {
        // Ajouter le rôle de base ROLE_USER lors de la création
        $this->roles[] = 'ROLE_USER';

        //Si l'user est majeur, Role supplémentaire ajouté pour plus d'options
        if ($this->is_adult) {
            $this->roles[] = 'ROLE_ADULT';
        }

        // Assurer l'unicité des rôles (pas de doublons)
        $this->roles = array_unique($this->roles);
    }

    #[ORM\PreUpdate]
    public function updateRolesOnModification(): void
    {
        // Si l'user est vérifié, role supp pour pouvoir avoir et voir ses bookmarked
        if ($this->isVerified && !in_array('ROLE_VERIFIED', $this->roles)) {
            $this->roles[] = 'ROLE_VERIFIED';
        }

        //Si l'user est majeur, Role supplémentaire ajouté pour plus d'options
        if ($this->is_adult && !in_array('ROLE_ADULT', $this->roles)) {
            $this->roles[] = 'ROLE_ADULT';
        }

        // Assurer l'unicité des rôles (pas de doublons)
        $this->roles = array_unique($this->roles);
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

    public function getRentedBooksCount(): ?int
    {
        return $this->rented_books_count;
    }

    public function setRentedBooksCount(int $rented_books_count): static
    {
        $this->rented_books_count = $rented_books_count;

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

    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        $this->books->removeElement($book);

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
