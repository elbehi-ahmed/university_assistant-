<?php

namespace App\Entity;

use App\Repository\AllowedEmailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AllowedEmailRepository::class)]
#[ORM\Table(name: 'allowed_email')]
class AllowedEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Role assigned to the user when they register with this email.
     * e.g. 'ROLE_STUDENT' | 'ROLE_TEACHER' | 'ROLE_ADMIN'
     */
    #[ORM\Column(length: 50)]
    private ?string $role = null;

    /**
     * Optional department information for teacher accounts.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    /**
     * Optional class information for student accounts.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $classe = null;

    /**
     * Set to true once the user has registered.
     * Used by RegisterController to prevent duplicate accounts.
     * Exposed in Twig as {{ allowed.isUsed }} (via isUsed() alias below).
     */
    #[ORM\Column]
    private bool $isRegistered = false;

    /**
     * Optional note for the admin (e.g. "Promotion 2025-GL3").
     * Used in admin/whitelist.html.twig : {{ allowed.note }}
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    /**
     * Date when the admin added this email to the whitelist.
     * Used in admin/whitelist.html.twig : {{ allowed.createdAt|date('d/m/Y') }}
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    // ──────────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────────

    public function __construct()
    {
        $this->createdAt = new \DateTime(); // auto-set on creation
    }

    // ──────────────────────────────────────────
    // Getters & Setters
    // ──────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $departement): static { $this->departement = $departement; return $this; }

    public function getClasse(): ?string { return $this->classe; }
    public function setClasse(?string $classe): static { $this->classe = $classe; return $this; }

    public function isRegistered(): bool { return $this->isRegistered; }
    public function setIsRegistered(bool $isRegistered): static { $this->isRegistered = $isRegistered; return $this; }

    /**
     * Twig-friendly alias for isRegistered().
     * admin/whitelist.html.twig uses {{ allowed.isUsed }} to check if an account was created.
     */
    public function isUsed(): bool { return $this->isRegistered; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }
}