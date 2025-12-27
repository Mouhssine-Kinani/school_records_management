<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessSubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        // Get user roles
        $roles = $user->getRoles();
        
        // Redirect based on role (check highest priority role first)
        if (in_array('ROLE_ADMIN', $roles)) {
            $redirectUrl = $this->urlGenerator->generate('admin_dashboard');
        } elseif (in_array('ROLE_ENSEIGNANT', $roles)) {
            $redirectUrl = $this->urlGenerator->generate('enseignant_dashboard');
        } elseif (in_array('ROLE_PARENT', $roles)) {
            $redirectUrl = $this->urlGenerator->generate('parent_dashboard');
        } elseif (in_array('ROLE_ELEVE', $roles)) {
            $redirectUrl = $this->urlGenerator->generate('eleve_dashboard');
        } else {
            // Fallback to login page if no recognized role
            $redirectUrl = $this->urlGenerator->generate('app_login');
        }

        $response = new RedirectResponse($redirectUrl);
        $event->setResponse($response);
    }
}