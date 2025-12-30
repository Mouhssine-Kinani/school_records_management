<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Security $security): Response
    {
        $user = $security->getUser();

        // Not authenticated → stay on /
        if (!$user) {
            return $this->render('welcome.html.twig');
        }

        // Role-based redirection
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($this->isGranted('ROLE_ENSEIGNANT')) {
            return $this->redirectToRoute('enseignant_dashboard');
        }

        if ($this->isGranted('ROLE_ELEVE')) {
            return $this->redirectToRoute('eleve_dashboard');
        }

        if ($this->isGranted('ROLE_PARENT')) {
            return $this->redirectToRoute('parent_dashboard');
        }
        
        // Safety fallback
        return $this->redirectToRoute('app_login');
    }

    
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
    
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name:"app_logout")]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
