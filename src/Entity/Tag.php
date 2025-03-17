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
        pattern: '/^[a-zA-Z]+$/',
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
     * @var Collection<int, Novel>
     */
    #[ORM\ManyToMany(targetEntity: Novel::class, inversedBy: 'tags')]
    private Collection $novels;

    public function __construct()
    {
        $this->novels = new ArrayCollection();
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
}
