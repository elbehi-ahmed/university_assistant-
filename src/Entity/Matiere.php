<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatiereRepository::class)]
class Matiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom de la matière. ex: "Mathématiques", "Algorithmique"
     * Used by StudentSubjectsController → subjects[].name
     * Used by TeacherClassesController  → resolveCategory()
     */
    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    /**
     * Coefficient de la matière.
     * Used by StudentSubjectsController → subjects[].coefficient
     */
    #[ORM\Column]
    private ?float $coefficient = null;

    /**
     * Classe FontAwesome stockée en base.
     * ex: "fa-square-root-alt"
     * If null, StudentSubjectsController resolves it via keyword map.
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $icone = null;

    /**
     * Couleur HSL stockée en base (hue 0-360).
     * ex: "38"
     * If null, StudentSubjectsController resolves it via keyword map.
     */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $couleur = null;

    /**
     * Professeur responsable de cette matière.
     * Used by TeacherDashboardController, TeacherClassesController, etc.
     */
    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?User $teacher = null;

    /**
     * Classe à laquelle appartient cette matière.
     * Used by TeacherClassesController → deduplicate classes via classe.id
     */
    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Classe $classe = null;

    #[ORM\OneToMany(mappedBy: 'matiere', targetEntity: Task::class, orphanRemoval: true)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'matiere', targetEntity: Grade::class, orphanRemoval: true)]
    private Collection $grades;

    #[ORM\OneToMany(mappedBy: 'matiere', targetEntity: Examen::class, orphanRemoval: true)]
    private Collection $examens;

    public function __construct()
    {
        $this->tasks   = new ArrayCollection();
        $this->grades  = new ArrayCollection();
        $this->examens = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getCoefficient(): ?float { return $this->coefficient; }
    public function setCoefficient(float $coefficient): static { $this->coefficient = $coefficient; return $this; }

    public function getIcone(): ?string { return $this->icone; }
    public function setIcone(?string $icone): static { $this->icone = $icone; return $this; }

    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(?string $couleur): static { $this->couleur = $couleur; return $this; }

    public function getTeacher(): ?User { return $this->teacher; }
    public function setTeacher(?User $teacher): static { $this->teacher = $teacher; return $this; }

    public function getClasse(): ?Classe { return $this->classe; }
    public function setClasse(?Classe $classe): static { $this->classe = $classe; return $this; }

    public function getTasks(): Collection   { return $this->tasks; }
    public function getGrades(): Collection  { return $this->grades; }
    public function getExamens(): Collection { return $this->examens; }
}