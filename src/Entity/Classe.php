<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $niveau = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $code = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'classes')]
    private Collection $etudiants;

    #[ORM\OneToMany(mappedBy: 'classe', targetEntity: Matiere::class)]
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

    public function getEtudiants(): Collection { return $this->etudiants; }
    public function getMatieres(): Collection { return $this->matieres; }

    public function addEtudiant(User $etudiant): static
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants->add($etudiant);
            $etudiant->addClass($this);
        }

        return $this;
    }

    public function removeEtudiant(User $etudiant): static
    {
        if ($this->etudiants->removeElement($etudiant)) {
            $etudiant->removeClass($this);
        }

        return $this;
    }

    public function addMatiere(Matiere $matiere): static
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres->add($matiere);
            $matiere->setClasse($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): static
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getClasse() === $this) {
                $matiere->setClasse(null);
            }
        }

        return $this;
    }
}
