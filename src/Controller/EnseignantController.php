<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\EnseignantMatiereClasseRepository;
use App\Repository\NoteRepository;
use App\Repository\InscriptionRepository;

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

        return $this->render('enseignant/dashboard.html.twig', [
            'user' => $enseignant,
            'affectations' => $affectations,
            'dernieresNotes' => $dernieresNotes,
        ]);
    }
}
