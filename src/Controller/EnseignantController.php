<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/enseignant')]
#[IsGranted('ROLE_ENSEIGNANT')]
class EnseignantController extends AbstractController
{
    #[Route('/dashboard', name: 'enseignant_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('enseignant/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}