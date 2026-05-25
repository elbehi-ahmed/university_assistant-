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
    // ── Priority constants ─────────────────────────────────────
    // Used in StudentDashboardController, StudentTasksController,
    // TeacherTasksController, StudentGradesController (badge/bar class mapping)
    public const PRIORITE_HIGH   = 'high';
    public const PRIORITE_MEDIUM = 'medium';
    public const PRIORITE_LOW    = 'low';

    // ── Status constants ───────────────────────────────────────
    // Used in StudentDashboardController (urgentTaskCount filter)
    // Used in TeacherTasksController (openCount, pendingCount)
    // Used in StudentGradesController (done filter)
    public const STATUT_OPEN   = 'open';
    public const STATUT_REVIEW = 'review';
    public const STATUT_CLOSED = 'closed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titre de la tâche / devoir.
     * Used by StudentTasksController   → tasks[].title
     * Used by TeacherTasksController   → tasks[].title
     * Used by StudentGradesController  → grades[].task
     */
    #[ORM\Column(length: 200)]
    private ?string $titre = null;

    /**
     * Description / consignes détaillées.
     * Used by StudentTasksController  → tasks[].description
     * Used by TeacherTasksController  → tasks[].subtitle
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Date limite de remise.
     * Used by StudentDashboardController → urgentTasks[].dueLabel
     * Used by StudentTasksController    → tasks[].deadline / tasks[].deadlineClass
     * Used by TeacherTasksController    → tasks[].deadline / tasks[].deadlineClass
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateLimite = null;

    /**
     * Priorité : 'high' | 'medium' | 'low'
     * Used by StudentDashboardController → urgentTaskCount (high + open)
     * Used by StudentTasksController    → tasks[].barClass / tasks[].badgeClass
     * Used by TeacherTasksController    → tasks[].priority / tasks[].priorityBadgeClass
     */
    #[ORM\Column(length: 10)]
    private string $priorite = self::PRIORITE_MEDIUM;

    /**
     * Statut : 'open' | 'review' | 'closed'
     * Used by StudentDashboardController → doneTaskCount (closed)
     * Used by TeacherTasksController    → tasks[].status / tasks[].statusLabel
     */
    #[ORM\Column(length: 10)]
    private string $statut = self::STATUT_OPEN;

    /**
     * Points maximum attribuables.
     * Used by TeacherTasksController → tasks[].maxPoints
     */
    #[ORM\Column(nullable: true)]
    private ?int $pointsMax = null;

    /**
     * Matière à laquelle appartient ce devoir.
     * Used by StudentDashboardController → urgentTasks[].subject
     * Used by StudentTasksController    → tasks[].subject
     * Used by TeacherTasksController    → (via matiere.classe.code → tasks[].classCode)
     */
    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Matiere $matiere = null;

    /** Soumissions des étudiants pour ce devoir */
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
    public function setDateLimite(\DateTimeInterface $dateLimite): static
    {
        $this->dateLimite = $dateLimite instanceof \DateTimeImmutable
            ? \DateTime::createFromInterface($dateLimite)
            : $dateLimite;

        return $this;
    }

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