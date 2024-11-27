<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\User;
use App\Form\PinType;
use App\Service\UserDashboardService; 
use App\Service\LoanService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
        private UserDashboardService $dashboardService;
        private NotificationService $notificationService;
        private LoanService $loanService;

    public function __construct(UserDashboardService $dashboardService, NotificationService $notificationService, LoanService $loanService)
    {
        $this->dashboardService = $dashboardService;
        $this->notificationService = $notificationService;
        $this->loanService = $loanService;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // Récupérer l'utilisateur actuel
        /** @var User $user */
        $user = $this->getUser();

        // Récupérer les notifications de l'utilisateur
        $notifications = $this->notificationService->getNotifications($user);
       
        // Appeler le service pour obtenir les données du dashboard
        $dashboardData = $this->dashboardService->getUserDashboardData($user);
        $unpaidLoan = $this->loanService->checkUnpaidLoans($user);
         //dd($dashboardData);
       
        if($dashboardData['userInfo']['isLocked']){

            return $this->render('dashboard/lockedUser.html.twig');

        }
        //calcul de la duree de l'echeance
        $today = new DateTimeImmutable();

        if($unpaidLoan){
            $duedate = $unpaidLoan->getDuedate();
            $days = $today->diff($duedate);
            $dueDaysInterval = $days->format('%d jours');

            //recuperation de la somme due
            $dueAmount = $unpaidLoan->getAmount();
            $dueDaysInterval = 0;
            if($dueDaysInterval == 0)
            {
                $this->addFlash('success', 'Emprunt :'.$dueAmount.',Prélevement de encours....');
                return $this->redirectToRoute('loan_repay');
            }
        }else{
            $dueAmount = 0;
            $dueDaysInterval = 0;
          
        }


        //prelevement automatique de l'emprunt 
       

        return $this->render('dashboard/index.html.twig', [
            'dashboard' => $dashboardData,
            'notifications' => $notifications,
            'dueDaysInterval' => $dueDaysInterval,
            'dueAmount' => $dueAmount
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