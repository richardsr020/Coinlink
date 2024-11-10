<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class P2pTradingController extends AbstractController
{
    #[Route('/p2p', name: 'app_p2p_trading')]
    public function index(): Response
    {
        return $this->render('p2p_trading/index.html.twig', [
            'controller_name' => 'P2pTradingController',
        ]);
    }
}
