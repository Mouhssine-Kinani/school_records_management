<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new \Exception('Unexpected user type');
        }

        $user->setMotDePasse($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Get teacher details with their assigned classes and student counts
     */
    public function getTeacherDetailsWithClasses(int $teacherId): ?array
    {
        // First, get the teacher
        $teacher = $this->find($teacherId);
        
        if (!$teacher || $teacher->getRole() !== 'enseignant') {
            return null;
        }

        // Now get all classes with student counts using DQL
        $query = $this->getEntityManager()->createQuery('
            SELECT emc, c, m, COUNT(DISTINCT i.id) as studentCount
            FROM App\Entity\EnseignantMatiereClasse emc
            JOIN emc.classe c
            JOIN emc.matiere m
            LEFT JOIN App\Entity\Inscription i WITH i.classe = c
            WHERE emc.enseignant = :teacherId
            GROUP BY emc.id, c.id, m.id
            ORDER BY c.nom ASC
        ')->setParameter('teacherId', $teacherId);

        $results = $query->getResult();
        
        $classes = [];
        foreach ($results as $row) {
            // $row[0] is the EnseignantMatiereClasse entity
            // $row['studentCount'] is the count
            $emc = $row[0];
            $classe = $emc->getClasse();
            $matiere = $emc->getMatiere();
            
            $classes[] = [
                'classe' => $classe,
                'matiere' => $matiere,
                'studentCount' => (int) $row['studentCount'],
                'anneeScolaire' => $emc->getAnneeScolaire()
            ];
        }

        return [
            'teacher' => $teacher,
            'classes' => $classes
        ];
    }

    /**
     * Find all students with their class for a specific school year
     */
    public function findAllElevesWithClass(string $anneeScolaire): array
    {
        // Get all students first
        $eleves = $this->findBy(
            ['role' => 'eleve'],
            ['nom' => 'ASC', 'prenom' => 'ASC']
        );

        // Get all inscriptions for this year in one query
        $inscriptions = $this->getEntityManager()
            ->getRepository('App\Entity\Inscription')
            ->createQueryBuilder('i')
            ->leftJoin('i.classe', 'c')
            ->addSelect('c')
            ->where('i.anneeScolaire = :year')
            ->setParameter('year', $anneeScolaire)
            ->getQuery()
            ->getResult();

        // Create a map of eleve_id => inscription for quick lookup
        $inscriptionMap = [];
        foreach ($inscriptions as $inscription) {
            $eleveId = $inscription->getEleve()->getId();
            $inscriptionMap[$eleveId] = $inscription;
        }

        // Build normalized results
        $results = [];
        foreach ($eleves as $eleve) {
            $eleveId = $eleve->getId();
            $inscription = $inscriptionMap[$eleveId] ?? null;
            
            $results[] = [
                'eleve' => $eleve,
                'classe' => $inscription ? $inscription->getClasse() : null,
                'statut' => $inscription ? $inscription->getStatut() : null
            ];
        }

        return $results;
    }
}
