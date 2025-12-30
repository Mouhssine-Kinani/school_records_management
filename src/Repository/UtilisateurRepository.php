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
        $qb = $this->createQueryBuilder('u')
            ->select('u', 'emc', 'c', 'm', 'COUNT(DISTINCT i.id) as studentCount')
            ->leftJoin('App\Entity\EnseignantMatiereClasse', 'emc', 'WITH', 'emc.enseignant = u.id')
            ->leftJoin('emc.classe', 'c')
            ->leftJoin('emc.matiere', 'm')
            ->leftJoin('App\Entity\Inscription', 'i', 'WITH', 'i.classe = c.id')
            ->where('u.id = :teacherId')
            ->andWhere('u.role = :role')
            ->setParameter('teacherId', $teacherId)
            ->setParameter('role', 'enseignant')
            ->groupBy('u.id, emc.id, c.id, m.id')
            ->getQuery();

        $results = $qb->getResult();
        
        if (empty($results)) {
            return null;
        }

        // Extract teacher and organize classes
        $teacher = null;
        $classes = [];
        
        foreach ($results as $result) {
            if ($teacher === null) {
                $teacher = $result[0];
            }
            
            // Only add classes that actually exist
            if (isset($result['emc']) && $result['emc'] !== null) {
                $emc = $result['emc'];
                $classe = $emc->getClasse();
                $matiere = $emc->getMatiere();
                
                if ($classe && $matiere) {
                    $classes[] = [
                        'classe' => $classe,
                        'matiere' => $matiere,
                        'studentCount' => $result['studentCount'] ?? 0,
                        'anneeScolaire' => $emc->getAnneeScolaire()
                    ];
                }
            }
        }

        return [
            'teacher' => $teacher,
            'classes' => $classes
        ];
    }
}
