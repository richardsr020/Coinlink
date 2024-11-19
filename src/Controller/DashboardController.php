<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\PinType;
use App\Service\UserDashboardService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
        private UserDashboardService $dashboardService;
        private NotificationService $notificationService;

    public function __construct(UserDashboardService $dashboardService, NotificationService $notificationService)
    {
        $this->dashboardService = $dashboardService;
        $this->notificationService = $notificationService;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // Récupérer l'utilisateur actuel
        /** @var User $user */
        $user = $this->getUser();

        // Récupérer les notifications de l'utilisateur
        $notifications = $this->notificationService->getNotifications($user);
        //dd($notifications);
        // Appeler le service pour obtenir les données du dashboard
        $dashboardData = $this->dashboardService->getUserDashboardData($user);
        //dd($dashboardData);
        return $this->render('dashboard/index.html.twig', [
            'dashboard' => $dashboardData,
            'notifications' => $notifications,
        ]);
    }

    #[Route('/dashboard/TransactionHistoryShow/{id}', name: 'transaction_history_show')]
    public function TransactionHistoryShow($id): Response
    {
        // Récupérer l'utilisateur actuel
        /** @var User $user */
        $user = $this->getUser();
     
        // Appeler le service pour obtenir les données du dashboard
        $transactionHistory = $this->dashboardService->getTransactionHistory($user);
    
        return $this->render('dashboard/showTransactionHistory.html.twig', [
            'transactionHystory' => $transactionHistory[$id],
         
        ]);
    }
}