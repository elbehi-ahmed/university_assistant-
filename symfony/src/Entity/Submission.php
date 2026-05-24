<?php

namespace App\Entity;

use App\Repository\SubmissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_submission', columns: ['task_id', 'student_id'])]
class Submission
{
    public const STATUT_SUBMITTED = 'submitted';
    public const STATUT_LATE      = 'late';
    public const STATUT_MISSING   = 'missing';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Task $task = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $student = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDepot = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_SUBMITTED;

    #[ORM\OneToOne(mappedBy: 'submission', targetEntity: Grade::class, cascade: ['persist', 'remove'])]
    private ?Grade $grade = null;

    public function getId(): ?int { return $this->id; }

    public function getTask(): ?Task { return $this->task; }
    public function setTask(?Task $task): static { $this->task = $task; return $this; }

    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): static { $this->student = $student; return $this; }

    public function getDateDepot(): ?\DateTimeInterface { return $this->dateDepot; }
    public function setDateDepot(\DateTimeInterface $dateDepot): static { $this->dateDepot = $dateDepot; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getGrade(): ?Grade { return $this->grade; }
}
