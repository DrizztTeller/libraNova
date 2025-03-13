<?php

namespace App\Entity;

use App\Repository\LoginHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoginHistoryRepository::class)]
class LoginHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'loginHistories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire.")]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de connexion est obligatoire.")]
    #[Assert\Type("\DateTimeImmutable", message: "La date de connexion doit être une date valide.")]
    private ?\DateTimeImmutable $login_date = null;
    #[ORM\Column(length: 255, nullable: true)]
    
    private ?string $ip_address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $device = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $os = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le navigateur est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le navigateur ne peut pas dépasser 255 caractères."
    )]
    private ?string $browser = null;

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

    public function getLoginDate(): ?\DateTimeImmutable
    {
        return $this->login_date;
    }

    public function setLoginDate(\DateTimeImmutable $login_date): static
    {
        $this->login_date = $login_date;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ip_address;
    }

    public function setIpAddress(?string $ip_address): static
    {
        $this->ip_address = $ip_address;
        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): static
    {
        $this->device = $device;
        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): static
    {
        $this->os = $os;
        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(string $browser): static
    {
        $this->browser = $browser;
        return $this;
    }
}
