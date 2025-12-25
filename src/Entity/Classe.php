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

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $niveau = null;

    #[ORM\Column(length: 20)]
    private ?string $anneeScolaire = null;

    /**
     * @var Collection<int, EnseignantMatiereClasse>
     */
    #[ORM\OneToMany(targetEntity: EnseignantMatiereClasse::class, mappedBy: 'classe')]
    private Collection $enseignantMatiereClasses;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'classe')]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->enseignantMatiereClasses = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getAnneeScolaire(): ?string
    {
        return $this->anneeScolaire;
    }

    public function setAnneeScolaire(string $anneeScolaire): static
    {
        $this->anneeScolaire = $anneeScolaire;

        return $this;
    }

    /**
     * @return Collection<int, EnseignantMatiereClasse>
     */
    public function getEnseignantMatiereClasses(): Collection
    {
        return $this->enseignantMatiereClasses;
    }

    public function addEnseignantMatiereClass(EnseignantMatiereClasse $enseignantMatiereClass): static
    {
        if (!$this->enseignantMatiereClasses->contains($enseignantMatiereClass)) {
            $this->enseignantMatiereClasses->add($enseignantMatiereClass);
            $enseignantMatiereClass->setClasse($this);
        }

        return $this;
    }

    public function removeEnseignantMatiereClass(EnseignantMatiereClasse $enseignantMatiereClass): static
    {
        if ($this->enseignantMatiereClasses->removeElement($enseignantMatiereClass)) {
            // set the owning side to null (unless already changed)
            if ($enseignantMatiereClass->getClasse() === $this) {
                $enseignantMatiereClass->setClasse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setClasse($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getClasse() === $this) {
                $inscription->setClasse(null);
            }
        }

        return $this;
    }
}
