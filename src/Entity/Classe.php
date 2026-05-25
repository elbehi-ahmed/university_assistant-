<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Nom complet de la classe. ex: "Informatique RT2 groupe A" */
    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    /**
     * Niveau / semestre. ex: "Semestre 3"
     * Exposed in TeacherClassesController as classes[].semester
     */
    #[ORM\Column(length: 50)]
    private ?string $niveau = null;

    /**
     * Code court unique. ex: "RT2-A"
     * Exposed in TeacherClassesController as classes[].code
     * Used in TeacherTasksController as tasks[].classCode
     * Used in TeacherStudentsController as classesList[].code
     */
    #[ORM\Column(length: 30, unique: true)]
    private ?string $code = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $schedule = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxStudents = null;

    /** Étudiants inscrits dans cette classe (inverse side of User.classes) */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'classes')]
    private Collection $etudiants;

    /** Matières enseignées dans cette classe */
    #[ORM\OneToMany(mappedBy: 'classe', targetEntity: Matiere::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $matieres;

    public function __construct()
    {
        $this->etudiants = new ArrayCollection();
        $this->matieres  = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(string $niveau): static { $this->niveau = $niveau; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $category): static { $this->category = $category; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getSchedule(): ?string { return $this->schedule; }
    public function setSchedule(?string $schedule): static { $this->schedule = $schedule; return $this; }

    public function getMaxStudents(): ?int { return $this->maxStudents; }
    public function setMaxStudents(?int $maxStudents): static { $this->maxStudents = $maxStudents; return $this; }

    public function getEtudiants(): Collection { return $this->etudiants; }
    public function getMatieres(): Collection  { return $this->matieres; }
}