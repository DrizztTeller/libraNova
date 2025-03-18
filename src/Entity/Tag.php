<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le nom du tag est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom du tag doit contenir au moins 2 caractères.",
        maxMessage: "Le nom du tag ne peut pas dépasser 255 caractères."
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\-\'\"\s]+$/',
        message: 'Le nom du tag ne doit contenir que des lettres alphabétiques.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description du tag est obligatoire.")]
    #[Assert\Length(
        min: 12,
        minMessage: "La description doit contenir au moins 12 caractères."
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\p{P}\p{Zs}_\-]+$/u',
        message: 'La description ne peut contenir que des lettres, des chiffres, des espaces et des signes de ponctuation.'
    )]
    private ?string $description = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, inversedBy: 'tags')]
    private Collection $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? 'Tag';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
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
}
