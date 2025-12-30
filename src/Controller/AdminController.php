<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\ClasseRepository;
use App\Repository\NoteRepository;
use App\Entity\Classe;
use App\Entity\EnseignantMatiereClasse;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Get unified menu items for admin sidebar
     */
    private function getMenuItems(): array
    {
        return [
            ['id' => 'dashboard', 'route' => 'admin_dashboard', 'icon' => 'dashboard', 'label' => 'Tableau de bord'],
            ['id' => 'students', 'route' => 'admin_dashboard', 'icon' => 'school', 'label' => 'Dossiers Élèves'],
            ['id' => 'teachers', 'route' => 'admin_enseignants', 'icon' => 'work', 'label' => 'Enseignants'],
            ['id' => 'classes', 'route' => 'admin_classes', 'icon' => 'domain', 'label' => 'Classes']
        ];
    }

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
            'menuItems' => $this->getMenuItems(),
            'activeMenu' => 'dashboard',
            'roleLabel' => 'Admin Panel',
            'pageTitle' => 'Tableau de bord',
        ]);
    }

    #[Route('/enseignants', name: 'admin_enseignants')]
    public function enseignants(UtilisateurRepository $utilisateurRepo): Response
    {
        $enseignants = $utilisateurRepo->findBy(['role' => 'enseignant']);

        return $this->render('admin/enseignant.html.twig', [
            'user' => $this->getUser(),
            'enseignants' => $enseignants,
            'menuItems' => $this->getMenuItems(),
            'activeMenu' => 'teachers',
            'roleLabel' => 'Admin Panel',
            'pageTitle' => 'Gestion des Enseignants',
        ]);
    }

    #[Route('/enseignants/{id}', name: 'admin_enseignant_details', requirements: ['id' => '\d+'])]
    public function enseignantDetails(int $id, UtilisateurRepository $utilisateurRepo): Response
    {
        $teacherData = $utilisateurRepo->getTeacherDetailsWithClasses($id);
        
        if (!$teacherData) {
            throw $this->createNotFoundException('Enseignant non trouvé');
        }

        return $this->render('admin/enseignantDetails.html.twig', [
            'user' => $this->getUser(),
            'teacher' => $teacherData['teacher'],
            'classes' => $teacherData['classes'],
            'menuItems' => $this->getMenuItems(),
            'activeMenu' => 'teachers',
            'roleLabel' => 'Admin Panel',
            'pageTitle' => 'Détails Enseignant',
        ]);
    }

    #[Route('/enseignants/create', name: 'admin_create_enseignant', methods: ['POST'])]
    public function createEnseignant(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepo
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation simple
            if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['mot_de_passe'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Veuillez remplir les champs obligatoires (Nom, Prénom, Email, Mot de passe).'
                ], 400);
            }

            // Vérifier si l'email existe déjà
            $existingUser = $utilisateurRepo->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé par un autre utilisateur.'
                ], 400);
            }

            // Création de le'nseignant
            $enseignant = new Utilisateur();
            $enseignant->setNom($data['nom']);
            $enseignant->setPrenom($data['prenom']);
            $enseignant->setEmail($data['email']);
            $enseignant->setRole('enseignant'); // Défini comme enseignant
            
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $enseignant,
                $data['mot_de_passe']
            );
            $enseignant->setMotDePasse($hashedPassword);

            // Champs optionnels
            if (!empty($data['telephone'])) {
                $enseignant->setTelephone($data['telephone']);
            }
            if (!empty($data['specialite'])) {
                $enseignant->setSpecialite($data['specialite']);
            }

            $em->persist($enseignant);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Enseignant ajouté avec succès.'
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage()
            ], 500);
        }
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
        
        return $this->render('admin/classes.html.twig', [
            'user' => $this->getUser(),
            'classes' => $classes,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'menuItems' => $this->getMenuItems(),
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

    #[Route('/classes/{id}/edit', name: 'admin_edit_class', methods: ['PUT'])]
    public function editClass(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepo,
        \App\Repository\MatiereRepository $matiereRepo,
        ClasseRepository $classeRepo
    ): JsonResponse {
        try {
            $classe = $classeRepo->find($id);
            if (!$classe) {
                return new JsonResponse(['success' => false, 'message' => 'Classe introuvable.'], 404);
            }

            $data = json_decode($request->getContent(), true);
            
            // Validate required fields
            if (empty($data['nom']) || empty($data['niveau']) || empty($data['annee_scolaire']) || 
                empty($data['enseignant_id']) || empty($data['matiere_id'])) {
                return new JsonResponse(['success' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
            }

            $currentYear = $classe->getAnneeScolaire();
            $newYear = $data['annee_scolaire'];

            // Update Class details
            $classe->setNom($data['nom']);
            $classe->setNiveau($data['niveau']);
            $classe->setAnneeScolaire($newYear);

            // Update Inscriptions if year changed
            if ($currentYear !== $newYear) {
                $em->createQuery('UPDATE App\Entity\Inscription i SET i.anneeScolaire = :newYear WHERE i.classe = :classe AND i.anneeScolaire = :currentYear')
                   ->setParameter('newYear', $newYear)
                   ->setParameter('classe', $classe)
                   ->setParameter('currentYear', $currentYear)
                   ->execute();
            }

            // Handle EnseignantMatiereClasse
            $teacher = $utilisateurRepo->find($data['enseignant_id']);
            $subject = $matiereRepo->find($data['matiere_id']);

            if (!$teacher || $teacher->getRole() !== 'enseignant') {
                return new JsonResponse(['success' => false, 'message' => 'Enseignant invalide.'], 400);
            }
            if (!$subject) {
                return new JsonResponse(['success' => false, 'message' => 'Matière invalide.'], 400);
            }

            $emcRepo = $em->getRepository(EnseignantMatiereClasse::class);
            
            // Check if exact match exists for new year
            $existingSpecific = $emcRepo->findOneBy([
                'classe' => $classe,
                'enseignant' => $teacher,
                'matiere' => $subject,
                'anneeScolaire' => $newYear
            ]);

            if (!$existingSpecific) {
                // If distinct match doesn't exist, try to update an old one
                // Look for one matching the OLD year to migrate
                $candidate = $emcRepo->findOneBy([
                    'classe' => $classe, 
                    'anneeScolaire' => $currentYear
                ]);

                if ($candidate) {
                    $candidate->setEnseignant($teacher);
                    $candidate->setMatiere($subject);
                    $candidate->setAnneeScolaire($newYear);
                } else {
                    // Create new if no candidate
                    $newEmc = new EnseignantMatiereClasse();
                    $newEmc->setClasse($classe);
                    $newEmc->setEnseignant($teacher);
                    $newEmc->setMatiere($subject);
                    $newEmc->setAnneeScolaire($newYear);
                    $em->persist($newEmc);
                }
            }

            $em->flush();

            // Cleanup: Delete any EMCs for this class that have the WRONG year (orphans)
            // This handles the case where we had multiple or where we reverted to an existing one leaving the modified one behind
            $em->createQuery('DELETE FROM App\Entity\EnseignantMatiereClasse emc WHERE emc.classe = :classe AND emc.anneeScolaire != :newYear')
               ->setParameter('classe', $classe)
               ->setParameter('newYear', $newYear)
               ->execute();

            return new JsonResponse(['success' => true, 'message' => 'Classe modifiée avec succès.']);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la modification: ' . $e->getMessage()], 500);
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
            
            // 1. Delete dependent EnseignantMatiereClasse records
            // We use DQL for efficiency or findBy + remove
            $em->createQuery('DELETE FROM App\Entity\EnseignantMatiereClasse emc WHERE emc.classe = :classe')
               ->setParameter('classe', $classe)
               ->execute();
               
            // 2. Delete dependent Inscription records (students in this class)
            $em->createQuery('DELETE FROM App\Entity\Inscription i WHERE i.classe = :classe')
               ->setParameter('classe', $classe)
               ->execute();
            
            // 3. Remove the class itself
            $em->remove($classe);
            $em->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Classe et données associées supprimées avec succès.'
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}