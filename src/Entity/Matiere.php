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

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $coefficient = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $icone = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $couleur = null;

    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?User $teacher = null;

    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
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

    public function getTasks(): Collection { return $this->tasks; }
    public function getGrades(): Collection { return $this->grades; }
    public function getExamens(): Collection { return $this->examens; }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setMatiere($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getMatiere() === $this) {
                $task->setMatiere(null);
            }
        }

        return $this;
    }

    public function addGrade(Grade $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setMatiere($this);
        }

        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        if ($this->grades->removeElement($grade)) {
            // set the owning side to null (unless already changed)
            if ($grade->getMatiere() === $this) {
                $grade->setMatiere(null);
            }
        }

        return $this;
    }

    public function addExamen(Examen $examen): static
    {
        if (!$this->examens->contains($examen)) {
            $this->examens->add($examen);
            $examen->setMatiere($this);
        }

        return $this;
    }

    public function removeExamen(Examen $examen): static
    {
        if ($this->examens->removeElement($examen)) {
            // set the owning side to null (unless already changed)
            if ($examen->getMatiere() === $this) {
                $examen->setMatiere(null);
            }
        }

        return $this;
    }
}
