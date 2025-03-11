<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $is_for_adult = null;

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
