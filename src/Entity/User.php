<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ──────────────────────────────────────────
    // Primary fields
    // ──────────────────────────────────────────

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(name: 'photo_profil', length: 255, nullable: true)]
    private ?string $photoProfil = null;

    /**
     * Account creation date — stored in DB.
     * BUG FIX: was #[ORM\Transient], so it was never persisted.
     * It was reset to "now" every time the entity was loaded, showing wrong dates.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Soft-delete / suspension flag — stored in DB.
     * BUG FIX: was #[ORM\Transient], so suspensions were lost on every page load.
     * Used in admin/users.html.twig: {% if not user.isActive %}Suspendu{% endif %}
     */
    #[ORM\Column]
    private bool $isActive = true;

    // ──────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────

    #[ORM\ManyToMany(targetEntity: Classe::class, inversedBy: 'etudiants')]
    #[ORM\JoinTable(name: 'user_classe')]
    private Collection $classes;

    /** Matières dont cet utilisateur est le professeur */
    #[ORM\OneToMany(mappedBy: 'teacher', targetEntity: Matiere::class)]
    private Collection $matieres;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Submission::class, orphanRemoval: true)]
    private Collection $submissions;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Grade::class, orphanRemoval: true)]
    private Collection $grades;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    // ──────────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────────

    public function __construct()
    {
        $this->classes       = new ArrayCollection();
        $this->matieres      = new ArrayCollection();
        $this->submissions   = new ArrayCollection();
        $this->grades        = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->createdAt     = new \DateTime(); // auto-set on creation, persisted to DB
    }

    // ──────────────────────────────────────────
    // UserInterface — required by Symfony Security
    // ──────────────────────────────────────────

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles   = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // No plain-text password stored on the entity.
    }

    // ──────────────────────────────────────────
    // Getters & Setters
    // ──────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getPhotoProfil(): ?string { return $this->photoProfil; }
    public function setPhotoProfil(?string $photoProfil): static { $this->photoProfil = $photoProfil; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    // ──────────────────────────────────────────
    // Classe relation helpers
    // ──────────────────────────────────────────

    public function getClasses(): Collection { return $this->classes; }

    public function addClass(Classe $classe): static
    {
        if (!$this->classes->contains($classe)) {
            $this->classes->add($classe);
        }
        return $this;
    }

    public function removeClass(Classe $classe): static
    {
        $this->classes->removeElement($classe);
        return $this;
    }

    // ──────────────────────────────────────────
    // Collection getters (read-only from outside)
    // ──────────────────────────────────────────

    public function getMatieres(): Collection      { return $this->matieres; }
    public function getSubmissions(): Collection   { return $this->submissions; }
    public function getGrades(): Collection        { return $this->grades; }
    public function getNotifications(): Collection { return $this->notifications; }
}
