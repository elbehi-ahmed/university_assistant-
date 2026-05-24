<?php

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'grade', targetEntity: Submission::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Submission $submission = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $student = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Matiere $matiere = null;

    #[ORM\Column]
    private ?float $valeur = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $lettre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private bool $isPublished = false;

    public function getId(): ?int { return $this->id; }

    public function getSubmission(): ?Submission { return $this->submission; }
    public function setSubmission(?Submission $submission): static { $this->submission = $submission; return $this; }

    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): static { $this->student = $student; return $this; }

    public function getMatiere(): ?Matiere { return $this->matiere; }
    public function setMatiere(?Matiere $matiere): static { $this->matiere = $matiere; return $this; }

    public function getValeur(): ?float { return $this->valeur; }
    public function setValeur(float $valeur): static { $this->valeur = $valeur; return $this; }

    public function getLettre(): ?string { return $this->lettre; }
    public function setLettre(?string $lettre): static { $this->lettre = $lettre; return $this; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }

    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }
}
