<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatiereRepository::class)]
class Matiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    private ?string $coefficient = null;

    #[ORM\Column]
    private ?int $nbrControle = null;

    /**
     * @var Collection<int, EnseignantMatiereClasse>
     */
    #[ORM\OneToMany(targetEntity: EnseignantMatiereClasse::class, mappedBy: 'matiere')]
    private Collection $enseignantMatiereClasses;

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'matiere')]
    private Collection $notes;

    public function __construct()
    {
        $this->enseignantMatiereClasses = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCoefficient(): ?string
    {
        return $this->coefficient;
    }

    public function setCoefficient(string $coefficient): static
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    public function getNbrControle(): ?int
    {
        return $this->nbrControle;
    }

    public function setNbrControle(int $nbrControle): static
    {
        $this->nbrControle = $nbrControle;

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
            $enseignantMatiereClass->setMatiere($this);
        }

        return $this;
    }

    public function removeEnseignantMatiereClass(EnseignantMatiereClasse $enseignantMatiereClass): static
    {
        if ($this->enseignantMatiereClasses->removeElement($enseignantMatiereClass)) {
            // set the owning side to null (unless already changed)
            if ($enseignantMatiereClass->getMatiere() === $this) {
                $enseignantMatiereClass->setMatiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setMatiere($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getMatiere() === $this) {
                $note->setMatiere(null);
            }
        }

        return $this;
    }
}
