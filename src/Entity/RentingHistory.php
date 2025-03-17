<?php

namespace App\Entity;

use App\Repository\RentingHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RentingHistoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class RentingHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rentings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire.")]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'rentings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le roman est obligatoire.")]
    private ?Novel $novel = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\Type(\DateTimeImmutable::class, message: "La date de début doit être une date valide.")]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de fin est obligatoire.")]
    #[Assert\Type(\DateTimeImmutable::class, message: "La date de fin doit être une date valide.")]
    private ?\DateTimeImmutable $end = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Regex(
        pattern: '/^\d+$|^terminé$/',
        message: 'Le numéro de la page où vous vous êtes arrêté dans votre lecture, si le livre est terminé, écrire "terminé"'
    )]
    private ?string $last_page = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class, message: "La date de mise à jour doit être une date valide.")]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\PrePersist]
    public function setDatesValue(): void
    {
        $this->start = new \DateTimeImmutable();
        $this->end = new \DateTimeImmutable("+5 days");
    }

    // Getters et setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getNovel(): ?Novel
    {
        return $this->novel;
    }

    public function setNovel(?Novel $novel): static
    {
        $this->novel = $novel;
        return $this;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTimeImmutable $start): static
    {
        $this->start = $start;
        return $this;
    }

    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(\DateTimeImmutable $end): static
    {
        $this->end = $end;
        return $this;
    }

    public function getLastPage(): ?string
    {
        return $this->last_page;
    }

    public function setLastPage(?string $last_page): static
    {
        $this->last_page = $last_page;
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
}
