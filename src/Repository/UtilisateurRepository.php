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
     * Only returns students who have at least one note
     * 
     * @param string $anneeScolaire The school year (e.g., '2023-2024')
     * @param int $page The page number (starts at 1)
     * @param int $limit The number of results per page (default: 30)
     * @return array Returns ['data' => array, 'total' => int, 'page' => int, 'limit' => int, 'totalPages' => int]
     */
    public function findAllElevesWithClass(string $anneeScolaire, int $page = 1, int $limit = 30, ?string $niveau = null): array
    {
        $offset = ($page - 1) * $limit;

        // Convert SQL query to Doctrine ORM QueryBuilder
        // Exact conversion of the provided SQL query with INNER JOINs
        // Note: The original SQL doesn't filter by anneeScolaire in WHERE clause
        $qb = $this->createQueryBuilder('u')
            ->select([
                'u.id as eleve_id',
                'u.nom as eleve_nom',
                'u.prenom as eleve_prenom',
                'u.email as eleve_email',
                'u.numeroInscription as eleve_numero_inscription',
                'c.id as classe_id',
                'c.nom as classe_nom',
                'i.statut as inscription_statut'
            ])
            ->innerJoin('App\Entity\Note', 'n', 'WITH', 'u.id = n.eleve')
            ->innerJoin('App\Entity\Inscription', 'i', 'WITH', 'u.id = i.eleve')
            ->innerJoin('App\Entity\Classe', 'c', 'WITH', 'i.classe = c.id')
            ->where('u.role = :role')
            ->setParameter('role', 'eleve');

        if ($niveau) {
            $qb->andWhere('c.niveau = :niveau')
                ->setParameter('niveau', $niveau);
        }

        $qb->groupBy('u.id, u.nom, u.prenom, u.email, u.numeroInscription, c.id, c.nom, i.statut')
            ->orderBy('u.id', 'ASC');

        // Get total count for pagination (separate query without GROUP BY)
        $countQb = $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->innerJoin('App\Entity\Note', 'n', 'WITH', 'u.id = n.eleve')
            ->innerJoin('App\Entity\Inscription', 'i', 'WITH', 'u.id = i.eleve')
            ->innerJoin('App\Entity\Classe', 'c', 'WITH', 'i.classe = c.id')
            ->where('u.role = :role')
            ->setParameter('role', 'eleve');

        if ($niveau) {
            $countQb->andWhere('c.niveau = :niveau')
                ->setParameter('niveau', $niveau);
        }

        try {
            $total = (int) $countQb->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $total = 0;
        }

        // Apply pagination
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $results = $qb->getQuery()->getResult();

        // Transform results to match expected structure
        $data = [];
        foreach ($results as $row) {
            // Create a minimal Utilisateur-like object structure
            $eleve = (object) [
                'id' => $row['eleve_id'],
                'nom' => $row['eleve_nom'],
                'prenom' => $row['eleve_prenom'],
                'email' => $row['eleve_email'],
                'numeroInscription' => $row['eleve_numero_inscription'] ?? null,
            ];

            // Create a minimal Classe-like object structure
            $classe = null;
            if (isset($row['classe_id']) && $row['classe_id'] !== null) {
                $classe = (object) [
                    'id' => $row['classe_id'],
                    'nom' => $row['classe_nom'] ?? null,
                ];
            }

            $data[] = [
                'eleve' => $eleve,
                'classe' => $classe,
                'statut' => $row['inscription_statut'] ?? null
            ];
        }

        $totalPages = (int) ceil($total / $limit);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ];
    }

    public function findElevesPaginated(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', 'eleve') // ✅ CORRECTION
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function countEleves(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.role = :role')
            ->setParameter('role', 'eleve') // ✅ CORRECTION
            ->getQuery()
            ->getSingleScalarResult();
    }
}
