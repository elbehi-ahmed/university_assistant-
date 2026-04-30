<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    public const PRIORITE_HIGH   = 'high';
    public const PRIORITE_MEDIUM = 'medium';
    public const PRIORITE_LOW    = 'low';

    public const STATUT_OPEN   = 'open';
    public const STATUT_REVIEW = 'review';
    public const STATUT_CLOSED = 'closed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateLimite = null;

    #[ORM\Column(length: 10)]
    private string $priorite = self::PRIORITE_MEDIUM;

    #[ORM\Column(length: 10)]
    private string $statut = self::STATUT_OPEN;

    #[ORM\Column(nullable: true)]
    private ?int $pointsMax = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Matiere $matiere = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Submission::class, orphanRemoval: true)]
    private Collection $submissions;

    public function __construct()
    {
        $this->submissions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getDateLimite(): ?\DateTimeInterface { return $this->dateLimite; }
    public function setDateLimite(\DateTimeInterface $dateLimite): static { $this->dateLimite = $dateLimite; return $this; }

    public function getPriorite(): string { return $this->priorite; }
    public function setPriorite(string $priorite): static { $this->priorite = $priorite; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getPointsMax(): ?int { return $this->pointsMax; }
    public function setPointsMax(?int $pointsMax): static { $this->pointsMax = $pointsMax; return $this; }

    public function getMatiere(): ?Matiere { return $this->matiere; }
    public function setMatiere(?Matiere $matiere): static { $this->matiere = $matiere; return $this; }

    public function getSubmissions(): Collection { return $this->submissions; }
}
