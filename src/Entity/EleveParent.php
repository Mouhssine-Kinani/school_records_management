<?php

namespace App\Entity;

use App\Repository\EleveParentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EleveParentRepository::class)]
#[ORM\Table(name: 'eleve_parent')]
#[ORM\UniqueConstraint(name: 'unique_eleve_parent', columns: ['eleve_id', 'parent_id'])]
class EleveParent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eleveParents')]
    private ?Utilisateur $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'eleveParents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $parent = null;

    #[ORM\Column(length: 20)]
    private ?string $relation = null;

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

    public function getParent(): ?Utilisateur
    {
        return $this->parent;
    }

    public function setParent(?Utilisateur $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getRelation(): ?string
    {
        return $this->relation;
    }

    public function setRelation(string $relation): static
    {
        $this->relation = $relation;

        return $this;
    }
}
