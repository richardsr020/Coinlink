<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TermsController extends AbstractController
{
    #[Route('/terms', name: 'app_terms')]
    public function index(): Response
    { 
        $siteName = $this->getParameter('site_name');
        return $this->render('terms/index.html.twig', [
            'controller_name' => 'TermsController',
            'site_name' => $siteName,  // Assure-toi que cette variable existe et fonctionne correctement
        ]);
    }
}
