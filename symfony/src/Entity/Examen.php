<?php

namespace App\Entity;

use App\Repository\ExamenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExamenRepository::class)]
class Examen
{
    // ── Type constants ─────────────────────────────────────────
    public const TYPE_DS      = 'DS';
    public const TYPE_EXAMEN  = 'examen';
    public const TYPE_RATRAP  = 'rattrapage';
    public const TYPE_QUIZ    = 'quiz';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Matière concernée.
     * Used by StudentExamsController → exam.subject.name (via matiere.getNom())
     * Used by TeacherDashboardController → upcomingExams[].subject
     */
    #[ORM\ManyToOne(inversedBy: 'examens')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Matiere $matiere = null;

    /**
     * Date ET heure de début de l'examen.
     * Used by StudentExamsController → exam.examDate (DateTime)
     *   → format('d')  for ebd-day
     *   → format('F')  for ebd-month
     *   → format('Y')  for ebd-year
     *   → format('H:i') for startTime
     * Used by TeacherDashboardController → upcomingExams[].date
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateExamen = null;

    /**
     * Heure de fin de l'examen.
     * Used by StudentExamsController → exam.endTime (format H:i)
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureFin = null;

    /**
     * Salle / lieu de l'examen. ex: "Salle A12"
     * Used by StudentExamsController → exam.room
     * Used by TeacherDashboardController → upcomingExams[].room
     */
    #[ORM\Column(length: 50)]
    private ?string $salle = null;

    /**
     * Type d'épreuve : 'DS' | 'examen' | 'rattrapage' | 'quiz'
     */
    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_EXAMEN;

    /**
     * Durée en minutes.
     * Used by StudentExamsController → exam.duration (e.g. "120 min")
     */
    #[ORM\Column]
    private ?int $dureeMinutes = null;

    // ──────────────────────────────────────────
    // Getters & Setters
    // ──────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getMatiere(): ?Matiere { return $this->matiere; }
    public function setMatiere(?Matiere $matiere): static { $this->matiere = $matiere; return $this; }

    public function getDateExamen(): ?\DateTimeInterface { return $this->dateExamen; }
    public function setDateExamen(\DateTimeInterface $dateExamen): static { $this->dateExamen = $dateExamen; return $this; }

    public function getHeureFin(): ?\DateTimeInterface { return $this->heureFin; }
    public function setHeureFin(\DateTimeInterface $heureFin): static { $this->heureFin = $heureFin; return $this; }

    public function getSalle(): ?string { return $this->salle; }
    public function setSalle(string $salle): static { $this->salle = $salle; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getDureeMinutes(): ?int { return $this->dureeMinutes; }
    public function setDureeMinutes(int $dureeMinutes): static { $this->dureeMinutes = $dureeMinutes; return $this; }
}