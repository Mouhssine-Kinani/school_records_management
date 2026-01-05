<?php

namespace App\Controller;

use App\Repository\ClasseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EnseignantMatiereClasseRepository;
use App\Repository\NoteRepository;
use App\Repository\InscriptionRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/enseignant')]
#[IsGranted('ROLE_ENSEIGNANT')]
class EnseignantController extends AbstractController
{
    #[Route('/dashboard', name: 'enseignant_dashboard')]
    public function dashboard(
        EnseignantMatiereClasseRepository $emcRepo,
        NoteRepository $noteRepo,
        InscriptionRepository $inscriptionRepo
    ): Response {
        $enseignant = $this->getUser();

        // Classes + matières de l’enseignant
        $affectations = $emcRepo->findBy([
            'enseignant' => $enseignant
        ]);

        // Dernières notes saisies par l’enseignant
        $dernieresNotes = $noteRepo->findBy(
            ['enseignant' => $enseignant],
            ['dateNote' => 'DESC'],
            5
        );

        // Récupérer tous les étudiants des classes de cet enseignant
        $classesIds = array_map(fn($a) => $a->getClasse()->getId(), $affectations);

        $nbEtudiants = 0;

        if (!empty($classesIds)) {
            $nbEtudiants = $inscriptionRepo->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->andWhere('i.classe IN (:classes)')
                ->setParameter('classes', $classesIds)
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $etudiants = [];
        }

        $nbNotesEnAttente = $noteRepo->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.enseignant = :enseignant')
            ->andWhere('n.valeur IS NULL')
            ->setParameter('enseignant', $enseignant)
            ->getQuery()
            ->getSingleScalarResult();


        $nbEtudiantsParClasse = [];

        foreach ($affectations as $affectation) {
            $classeId = $affectation->getClasse()->getId();

            if (!isset($nbEtudiantsParClasse[$classeId])) {
                $nbEtudiantsParClasse[$classeId] = $inscriptionRepo->createQueryBuilder('i')
                    ->select('COUNT(i.id)')
                    ->andWhere('i.classe = :classe')
                    ->setParameter('classe', $classeId)
                    ->getQuery()
                    ->getSingleScalarResult();
            }
        }

        $matieres = [];

        foreach ($affectations as $affectation) {
            $matiere = $affectation->getMatiere();
            $matiereId = $matiere->getId();

            if (!isset($matieres[$matiereId])) {
                $matieres[$matiereId] = [
                    'matiere' => $matiere,
                    'classes' => [],
                ];
            }

            $matieres[$matiereId]['classes'][] = $affectation->getClasse();
        }

        return $this->render('enseignant/dashboard.html.twig', [
            'user' => $enseignant,
            'affectations' => $affectations,
            'dernieresNotes' => $dernieresNotes,
            'nbEtudiants' => $nbEtudiants,
            'nbNotesEnAttente' => $nbNotesEnAttente,
            'nbEtudiantsParClasse' => $nbEtudiantsParClasse,
            'matieres' => $matieres,
        ]);
    }

    #[Route('/enseignant/gestion-classes', name: 'enseignant_gestion_classes')]
    public function gestionClasses(
        EnseignantMatiereClasseRepository $emcRepository,
        Request $request
    ): Response {
        $enseignant = $this->getUser();

        // Récupération des filtres depuis la requête GET
        $niveau = $request->query->get('niveau');        // Filtre par niveau (6,5,4,3)
        $q = $request->query->get('q');                 // Recherche par nom de classe

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;                                     // nombre de classes par page
        $offset = ($page - 1) * $limit;

        // Récupération des classes filtrées et paginées
        $relations = $emcRepository->findByEnseignantNiveauEtRecherche(
            $enseignant,
            $niveau,
            $q,
            $limit,
            $offset
        );

        // Nombre total pour la pagination
        $total = $emcRepository->countByEnseignantNiveauEtRecherche(
            $enseignant,
            $niveau,
            $q
        );

        // Rendu Twig
        return $this->render('enseignant/gestion_classes.html.twig', [
            'relations' => $relations,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'niveau' => $niveau,
            'q' => $q,           // pour pré-remplir l'input de recherche
        ]);
    }

    #[Route('/enseignant/gestion-etudiants', name: 'enseignant_gestion_etudiants')]
    public function gestionEtudiants(
        Request $request,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $totalEleves = $utilisateurRepository->countEleves(); // méthode perso
        $eleves = $utilisateurRepository->findElevesPaginated($limit, $offset);

        $totalPages = ceil($totalEleves / $limit);
        $start = $offset + 1;
        $end = min($offset + $limit, $totalEleves);

        return $this->render('enseignant/gestion_etudiants.html.twig', [
            'eleves' => $eleves,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalEleves' => $totalEleves,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
