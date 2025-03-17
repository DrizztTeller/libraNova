<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: BookRepository::class)]
#[UniqueEntity(fields: ['ref'], message: 'Cette référence est déjà utilisée.')]
#[ORM\HasLifecycleCallbacks]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_\s\-éèêëàâäîïôöùûüçñÑ&µ@$£€*%!?,;:\'".^°()#+\/]{2,255}$/',
        message: "Le titre ne peut contenir que des lettres, les lettres minuscules avec accents, des chiffres, des espaces, des traits d'union, des underscores et les symboles : &, µ, @, $, £, €, *, %, !, ?, ;, :, \', \", ^, °, (, ), +, /, . et #"
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'auteur est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom de l\'auteur doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom de l\'auteur ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_\s\-éèêëàâäîïôöùûüçñÑ&µ@$£€*%!?,;:\'".^°()#+\/]{2,255}$/',
        message: "Le nom de l'auteur ne peut contenir que des lettres, les lettres minuscules avec accents, des chiffres, des espaces, des traits d'union, des underscores et les symboles : &, µ, @, $, £, €, *, %, !, ?, ;, :, \', \", ^, °, (, ), +, /, . et #"
    )]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le résumé est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le résumé doit contenir au moins {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z].[a-zA-Z0-9_\s\-éèêëàâäîïôöùûüçñÑ&µ@$£€*%!?,;:\'".^°()#+\/]{9,}\.$/',
        message: "Le résumé doit commencer par une majuscule, se doit d'avoir au moins 10 caractères et doit se terminer par un point."
    )]
    private ?string $abstract = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'L\'état de publication est obligatoire.')]
    private bool $is_published = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Type(
        type: \DateTimeInterface::class,
        message: 'La date de sortie doit être une date valide.'
    )]
    private ?\DateTimeInterface $released_at = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de création est obligatoire.')]
    #[Assert\Type(
        type: \DateTimeImmutable::class,
        message: 'La date de création doit être une date valide.'
    )]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(
        type: \DateTimeImmutable::class,
        message: 'La date de mise à jour doit être une date valide.'
    )]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255)]
    private ?string $pic = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:\-[a-z0-9]+)*$/',
        message: 'Le slug ne peut contenir que des lettres minuscules, des chiffres et des tirets.'
    )]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La référence est obligatoire.')]
    private ?string $ref = null;

    #[ORM\Column(length: 13, nullable: true)]
    #[Assert\Isbn(
        isbn10Message: 'L\'ISBN-10 fourni est invalide.',
        isbn13Message: 'L\'ISBN-13 fourni est invalide.',
        bothIsbnMessage: 'Veuillez entrer un ISBN valide (ISBN-10 ou ISBN-13).'
    )]
    private ?string $isbn = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'L\'indication adulte est obligatoire.')]
    private bool $is_for_adult = true;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'books')]
    private Collection $tags;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'books')]
    private Collection $likes;

    #[ORM\OneToMany(targetEntity: RentingHistory::class, mappedBy: 'book')]
    private Collection $rentings;

    public function __construct(private SluggerInterface $slugger)
    {
        $this->tags = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->rentings = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlugAndReference(): void
    {
        if (!empty($this->title)) {
            $newSlug = strtolower($this->slugger->slug($this->title)->toString());
    
            // Vérifie si le slug a changé
            if ($this->slug !== $newSlug) {
                $this->slug = $newSlug;
                $this->ref = uniqid($this->slug . '_', true); // Génère une nouvelle ref
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    public function setAbstract(string $abstract): static
    {
        $this->abstract = $abstract;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->is_published;
    }

    public function setIsPublished(bool $is_published): static
    {
        $this->is_published = $is_published;

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeInterface
    {
        return $this->released_at;
    }

    public function setReleasedAt(\DateTimeInterface $released_at): static
    {
        $this->released_at = $released_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getPic(): ?string
    {
        return $this->pic;
    }

    public function setPic(string $pic): static
    {
        $this->pic = $pic;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function isForAdult(): ?bool
    {
        return $this->is_for_adult;
    }

    public function setIsForAdult(bool $is_for_adult): static
    {
        $this->is_for_adult = $is_for_adult;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addBook($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeBook($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(User $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->addBook($this);
        }

        return $this;
    }

    public function removeLike(User $like): static
    {
        if ($this->likes->removeElement($like)) {
            $like->removeBook($this);
        }

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
            $renting->setBook($this);
        }

        return $this;
    }

    public function removeRenting(RentingHistory $renting): static
    {
        if ($this->rentings->removeElement($renting)) {
            // set the owning side to null (unless already changed)
            if ($renting->getBook() === $this) {
                $renting->setBook(null);
            }
        }

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): static
    {
        $this->isbn = $isbn;

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
}
