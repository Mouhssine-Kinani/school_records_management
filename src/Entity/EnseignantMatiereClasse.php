<?php

namespace App\Entity;

use App\Repository\EnseignantMatiereClasseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnseignantMatiereClasseRepository::class)]
#[ORM\Table(name: 'enseignant_matiere_classe')]
#[ORM\UniqueConstraint(name: 'unique_ens_mat_classe_annee', columns: ['enseignant_id', 'matiere_id', 'classe_id', 'annee_scolaire'])]
class EnseignantMatiereClasse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enseignantMatiereClasses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $enseignant = null;

    #[ORM\ManyToOne(inversedBy: 'enseignantMatiereClasses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matiere $matiere = null;

    #[ORM\ManyToOne(inversedBy: 'enseignantMatiereClasses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Classe $classe = null;

    #[ORM\Column(length: 20)]
    private ?string $anneeScolaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnseignant(): ?Utilisateur
    {
        return $this->enseignant;
    }

    public function setEnseignant(?Utilisateur $enseignant): static
    {
        $this->enseignant = $enseignant;

        return $this;
    }

    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    public function setMatiere(?Matiere $matiere): static
    {
        $this->matiere = $matiere;

        return $this;
    }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): static
    {
        $this->classe = $classe;

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
}
