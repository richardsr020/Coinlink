<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
    
        $error = $authenticationUtils->getLastAuthenticationError();
        $errorMessage = null;
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();        
            if($error){
                $errorMessage = 'Identifiants invalides veuillez reessayer ';
                $this->addFlash('error', $errorMessage);
                $lastUsername = null; // Clear the username to prevent security issues.
            }
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
