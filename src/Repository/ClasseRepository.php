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
     * @return array
     */
    public function getClassesWithDetails(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                c.id,
                c.nom,
                c.niveau,
                c.annee_scolaire,
                COUNT(DISTINCT i.id) as student_count,
                u.id as teacher_id,
                u.nom as teacher_nom,
                u.prenom as teacher_prenom,
                m.id as matiere_id,
                m.libelle as matiere_libelle
            FROM classe c
            LEFT JOIN enseignant_matiere_classe emc ON c.id = emc.classe_id
            LEFT JOIN inscription i ON c.id = i.classe_id 
                AND i.annee_scolaire = c.annee_scolaire 
                AND i.statut = :statut
            LEFT JOIN utilisateur u ON emc.enseignant_id = u.id AND u.role = :role
            LEFT JOIN matiere m ON emc.matiere_id = m.id
            GROUP BY c.id, c.nom, c.niveau, c.annee_scolaire, u.id, u.nom, u.prenom, m.id, m.libelle
            ORDER BY c.niveau ASC, c.nom ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'statut' => 'active',
            'role' => 'enseignant'
        ]);
        
        return $result->fetchAllAssociative();
    }
}
