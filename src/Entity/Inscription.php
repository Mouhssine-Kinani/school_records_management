<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
#[ORM\Table(name: 'inscription')]
#[ORM\UniqueConstraint(name: 'unique_eleve_annee', columns: ['eleve_id', 'annee_scolaire'])]

class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Classe $classe = null;

    #[ORM\Column(length: 20)]
    private ?string $anneeScolaire = null;  // ✅ CORRECT (camelCase + orthographe correcte)

    #[ORM\Column]
    private ?\DateTime $DateInscription = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEleve(): ?Utilisateur
    {
        return $this->eleve;
    }

    public function setEleve(?Utilisateur $eleve): static
    {
        $this->eleve = $eleve;

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

    public function getAnneeScolaire(): ?string  // ✅ pas getAnneScolaire
    {
        return $this->anneeScolaire;
    }

    public function setAnneeScolaire(string $anneeScolaire): static  // ✅ pas setAnneScolaire
    {
        $this->anneeScolaire = $anneeScolaire;
        return $this;
    }

    public function getDateInscription(): ?\DateTime
    {
        return $this->DateInscription;
    }

    public function setDateInscription(\DateTime $DateInscription): static
    {
        $this->DateInscription = $DateInscription;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
