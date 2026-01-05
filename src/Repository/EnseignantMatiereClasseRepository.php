<?php

namespace App\Repository;

use App\Entity\EnseignantMatiereClasse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Utilisateur;

/**
 * @extends ServiceEntityRepository<EnseignantMatiereClasse>
 */
class EnseignantMatiereClasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnseignantMatiereClasse::class);
    }

    /**
     * Récupère les classes d’un enseignant avec filtre par niveau + pagination
     */
    public function findByEnseignantAndNiveau(
        Utilisateur $enseignant,
        ?string $niveau,
        int $limit,
        int $offset
    ): array {
        $qb = $this->createQueryBuilder('emc')
            ->join('emc.classe', 'c')
            ->addSelect('c')
            ->where('emc.enseignant = :enseignant')
            ->setParameter('enseignant', $enseignant)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('c.nom', 'ASC');

        if ($niveau) {
            $qb->andWhere('c.niveau LIKE :niveau')
                ->setParameter('niveau', $niveau . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les classes d’un enseignant avec filtre par niveau
     */
    public function countByEnseignantAndNiveau(
        Utilisateur $enseignant,
        ?string $niveau
    ): int {
        $qb = $this->createQueryBuilder('emc')
            ->select('COUNT(emc.id)')
            ->join('emc.classe', 'c')
            ->where('emc.enseignant = :enseignant')
            ->setParameter('enseignant', $enseignant);

        if ($niveau) {
            $qb->andWhere('c.niveau LIKE :niveau')
                ->setParameter('niveau', $niveau . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByEnseignantNiveauEtRecherche(
        Utilisateur $enseignant,
        ?string $niveau,
        ?string $q,
        int $limit,
        int $offset
    ): array {
        $qb = $this->createQueryBuilder('emc')
            ->join('emc.classe', 'c')
            ->addSelect('c')
            ->where('emc.enseignant = :enseignant')
            ->setParameter('enseignant', $enseignant)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('c.nom', 'ASC');

        if ($niveau) {
            $qb->andWhere('c.niveau LIKE :niveau')
                ->setParameter('niveau', $niveau . '%');
        }

        if ($q) {
            $qb->andWhere('c.nom LIKE :q')
                ->setParameter('q', '%' . $q . '%'); // recherche partielle
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les classes d’un enseignant avec filtre par niveau + recherche
     */
    public function countByEnseignantNiveauEtRecherche(
        Utilisateur $enseignant,
        ?string $niveau,
        ?string $q
    ): int {
        $qb = $this->createQueryBuilder('emc')
            ->select('COUNT(emc.id)')
            ->join('emc.classe', 'c')
            ->where('emc.enseignant = :enseignant')
            ->setParameter('enseignant', $enseignant);

        if ($niveau) {
            $qb->andWhere('c.niveau LIKE :niveau')
                ->setParameter('niveau', $niveau . '%');
        }

        if ($q) {
            $qb->andWhere('c.nom LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    //    /**
    //     * @return EnseignantMatiereClasse[] Returns an array of EnseignantMatiereClasse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EnseignantMatiereClasse
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
