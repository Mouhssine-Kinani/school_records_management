<?php

namespace App\Repository;

use App\Entity\Classe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Classe>
 */
class ClasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classe::class);
    }

    /**
     * Get all classes with student count and principal teacher info
     * Optimized using Doctrine QueryBuilder with partial selects
     * @return array
     */
    public function getClassesWithDetails(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'c.id',
                'c.nom',
                'c.niveau',
                'c.anneeScolaire as annee_scolaire',
                'COUNT(DISTINCT i.id) as student_count',
                'IDENTITY(emc.enseignant) as teacher_id',
                'u.nom as teacher_nom',
                'u.prenom as teacher_prenom',
                'IDENTITY(emc.matiere) as matiere_id',
                'm.libelle as matiere_libelle'
            )
            ->leftJoin('App\Entity\EnseignantMatiereClasse', 'emc', 'WITH', 'c.id = emc.classe')
            ->leftJoin('App\Entity\Inscription', 'i', 'WITH', 
                'c.id = i.classe AND i.anneeScolaire = c.anneeScolaire AND i.statut = :statut')
            ->leftJoin('App\Entity\Utilisateur', 'u', 'WITH', 
                'emc.enseignant = u.id AND u.role = :role')
            ->leftJoin('App\Entity\Matiere', 'm', 'WITH', 'emc.matiere = m.id')
            ->setParameter('statut', 'active')
            ->setParameter('role', 'enseignant')
            ->groupBy('c.id, c.nom, c.niveau, c.anneeScolaire, emc.enseignant, u.nom, u.prenom, emc.matiere, m.libelle')
            ->orderBy('c.niveau', 'ASC')
            ->addOrderBy('c.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
