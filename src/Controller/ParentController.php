<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parent')]
#[IsGranted('ROLE_PARENT')]
class ParentController extends AbstractController
{
    #[Route('/dashboard', name: 'parent_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('parent/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}