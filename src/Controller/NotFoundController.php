<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotFoundController extends AbstractController
{
    #[Route('/notFound', name: 'app_notFound')]
    public function notFound(): Response
    {
        return $this->render('notFound/notFound.html.twig', [
            'controller_name' => 'notFound',
        ]);
    }
}
