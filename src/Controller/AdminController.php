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
}