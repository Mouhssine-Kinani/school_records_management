<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\ClasseRepository;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
    public function classes(ClasseRepository $classeRepo): Response
    {
        $classes = $classeRepo->getClassesWithDetails();
        
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
            'menuItems' => $menuItems,
            'activeMenu' => 'classes',
            'roleLabel' => 'Admin Panel',
            'pageTitle' => 'Gestion des Classes',
        ]);
    }
}