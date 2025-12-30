<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Calculate the overall average grade from all notes
     */
    public function getMoyenneGenerale(): ?float
    {
        $result = $this->createQueryBuilder('n')
            ->select('AVG(n.valeur) as moyenne')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : null;
    }

    /**
     * Get monthly averages for the current school year
     * Uses native SQL as MONTH() is not supported in standard DQL
     * Returns an array with month names and their corresponding averages
     */
    public function getMonthlyAverages(): array
    {
        // School year typically runs Sept to June
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        
        // If we're in Jan-Aug, school year started last year
        $schoolYearStart = $currentMonth < 9 
            ? new \DateTime(($currentYear - 1) . '-09-01')
            : new \DateTime($currentYear . '-09-01');
        
        $schoolYearEnd = (clone $schoolYearStart)->modify('+10 months'); // Through June

        // Use native SQL - MONTH() function is MySQL-specific and not in standard DQL
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT MONTH(date_note) as month, AVG(valeur) as moyenne
            FROM note
            WHERE date_note BETWEEN :start AND :end
            GROUP BY MONTH(date_note)
            ORDER BY MONTH(date_note) ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'start' => $schoolYearStart->format('Y-m-d'),
            'end' => $schoolYearEnd->format('Y-m-d')
        ])->fetchAllAssociative();

        // Map month numbers to French names (school year order: Sept-June)
        $monthNames = [
            9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc',
            1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr',
            5 => 'Mai', 6 => 'Juin'
        ];

        $monthlyData = [];
        foreach ($results as $result) {
            $monthNum = (int) $result['month'];
            if (isset($monthNames[$monthNum])) {
                $monthlyData[] = [
                    'month' => $monthNames[$monthNum],
                    'average' => round((float) $result['moyenne'], 2)
                ];
            }
        }

        return $monthlyData;
    }

    /**
     * Get recent activities (latest notes added)
     * Returns the 10 most recent notes with teacher, student, and subject info
     */
    public function getRecentActivities(int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->select('n', 'e', 'eleve', 'm')
            ->leftJoin('n.enseignant', 'e')
            ->leftJoin('n.eleve', 'eleve')
            ->leftJoin('n.matiere', 'm')
            ->orderBy('n.dateNote', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Note[] Returns an array of Note objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Note
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
