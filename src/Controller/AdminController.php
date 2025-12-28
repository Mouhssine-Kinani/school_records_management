<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\ClasseRepository;
use App\Repository\NoteRepository;
use App\Entity\Classe;
use App\Entity\EnseignantMatiereClasse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        UtilisateurRepository $utilisateurRepo,
        ClasseRepository $classeRepo,
        NoteRepository $noteRepo
    ): Response {
        // Fetch statistics from database
        $stats = [
            'totalEleves' => $utilisateurRepo->count(['role' => 'eleve']),
            'totalEnseignants' => $utilisateurRepo->count(['role' => 'enseignant']),
            'totalClasses' => $classeRepo->count([]),
            'moyenneGenerale' => $noteRepo->getMoyenneGenerale() ?? 0,
        ];

        // Fetch monthly averages for the chart
        $monthlyAverages = $noteRepo->getMonthlyAverages();

        // Fetch recent activities
        $recentActivities = $noteRepo->getRecentActivities(10);

        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
            'stats' => $stats,
            'monthlyAverages' => $monthlyAverages,
            'recentActivities' => $recentActivities,
        ]);
    }

    #[Route('/classes', name: 'admin_classes')]
    public function classes(
        ClasseRepository $classeRepo,
        UtilisateurRepository $utilisateurRepo,
        \App\Repository\MatiereRepository $matiereRepo
    ): Response {
        $classes = $classeRepo->getClassesWithDetails();
        
        // Fetch teachers and subjects for the modal
        $teachers = $utilisateurRepo->findBy(['role' => 'enseignant']);
        $subjects = $matiereRepo->findAll();
        
        // Define menu items for sidebar
        $menuItems = [
            ['id' => 'dashboard', 'label' => 'Tableau de bord', 'icon' => 'dashboard', 'route' => 'admin_dashboard'],
            ['id' => 'students', 'label' => 'Étudiants', 'icon' => 'school', 'route' => 'admin_dashboard'],
            ['id' => 'teachers', 'label' => 'Enseignants', 'icon' => 'person', 'route' => 'admin_dashboard'],
            ['id' => 'classes', 'label' => 'Classes', 'icon' => 'meeting_room', 'route' => 'admin_classes'],
            ['id' => 'schedule', 'label' => 'Emploi du temps', 'icon' => 'calendar_month', 'route' => 'admin_dashboard'],
        ];

        return $this->render('admin/classes.html.twig', [
            'user' => $this->getUser(),
            'classes' => $classes,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'menuItems' => $menuItems,
            'activeMenu' => 'classes',
            'roleLabel' => 'Admin Panel',
            'pageTitle' => 'Gestion des Classes',
        ]);
    }

    #[Route('/classes/create', name: 'admin_create_class', methods: ['POST'])]
    public function createClass(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepo,
        \App\Repository\MatiereRepository $matiereRepo,
        ClasseRepository $classeRepo
    ): JsonResponse {
        try {
            // Get JSON data from request
            $data = json_decode($request->getContent(), true);
            
            // Validate required fields
            if (empty($data['nom']) || empty($data['niveau']) || empty($data['annee_scolaire']) || 
                empty($data['enseignant_id']) || empty($data['matiere_id'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Tous les champs sont obligatoires.'
                ], 400);
            }
            
            // Check if class already exists
            $existingClass = $classeRepo->findOneBy([
                'nom' => $data['nom'],
                'niveau' => $data['niveau'],
                'anneeScolaire' => $data['annee_scolaire']
            ]);
            
            if ($existingClass) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Une classe avec ce nom, niveau et année scolaire existe déjà.'
                ], 400);
            }
            
            // Find teacher
            $teacher = $utilisateurRepo->find($data['enseignant_id']);
            if (!$teacher || $teacher->getRole() !== 'enseignant') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Enseignant invalide.'
                ], 400);
            }
            
            // Find subject
            $subject = $matiereRepo->find($data['matiere_id']);
            if (!$subject) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Matière invalide.'
                ], 400);
            }
            
            // Create new Classe
            $classe = new Classe();
            $classe->setNom($data['nom']);
            $classe->setNiveau($data['niveau']);
            $classe->setAnneeScolaire($data['annee_scolaire']);
            
            // Persist the class
            $em->persist($classe);
            $em->flush();
            
            // Create EnseignantMatiereClasse relationship
            $enseignantMatiereClasse = new EnseignantMatiereClasse();
            $enseignantMatiereClasse->setEnseignant($teacher);
            $enseignantMatiereClasse->setMatiere($subject);
            $enseignantMatiereClasse->setClasse($classe);
            $enseignantMatiereClasse->setAnneeScolaire($data['annee_scolaire']);
            
            // Persist the relationship
            $em->persist($enseignantMatiereClasse);
            $em->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Classe créée avec succès.',
                'classe_id' => $classe->getId()
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/classes/{id}/delete', name: 'admin_delete_class', methods: ['DELETE'])]
    public function deleteClass(
        int $id,
        EntityManagerInterface $em,
        ClasseRepository $classeRepo
    ): JsonResponse {
        try {
            // Find the class
            $classe = $classeRepo->find($id);
            
            if (!$classe) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Classe introuvable.'
                ], 404);
            }
            
            // Remove the class (cascade will handle related records)
            $em->remove($classe);
            $em->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Classe supprimée avec succès.'
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}