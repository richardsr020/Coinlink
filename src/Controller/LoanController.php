<?php

namespace App\Controller;

use App\Entity\Loan;
use App\Form\LoanType;
use App\Form\PinType;
use App\Service\LoanService;
use App\Service\NotificationService;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LoanController extends AbstractController
{
    private LoanService $loanService;
    private NotificationService $notificationService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LoanService $loanService,
        NotificationService $notificationService,
        EntityManagerInterface $entityManager
    ) {
        $this->loanService = $loanService;
        $this->notificationService = $notificationService;
        $this->entityManager = $entityManager;
    }

    #[Route('/loan/request', name: 'loan_request')]
    public function loanRequest(Request $request, SessionInterface $session): Response
    {
        $loan = new Loan();
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $data = $form->getData();

            // Obtenir la somme de prêt
            $amount = $data->getAmount();
            $accountId = $data->getAccountid();

            // Ajouter les données à la session
            $session->set('loan_data', [
                'amount' => $amount,
                'accountId' => $accountId
            ]);

            return $this->redirectToRoute('app_loan_confirm');
        }

        return $this->render('loan/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/loan/confirm/{decline?}', name: 'app_loan_confirm')]
    public function loanConfirm(Request $request, SessionInterface $session, $decline=null): Response
    {
        $loanData = $session->get('loan_data');
        $user = $this->getUser();
        $roules = $this->loanService->getLoanRules($loanData ['amount']);
        

        //informations detaillés concernant le pret
        $name = $user->getName();
        $email = $user->getEmail();
        $duration = $roules ['durationDays'];
        $loanInterest = $roules ['interestRate'];
        $amount = $loanData ['amount'];
        $accountId = $loanData ['accountId'];
        


        if (!$loanData) {
            $this->addFlash('error', 'Veuillez remplir le formulaire.');
            return $this->redirectToRoute('loan_request');
        }

        if ($decline) {
            $session->remove('loan_data');
            $this->addFlash('success', 'La demande de prêt a été annulée.');
            return $this->redirectToRoute('loan_request');
        }

        return $this->render('loan/confirm.html.twig', [
            'name' => $name,
            'email' => $email,
            'duration' => $duration,
            'loanInterest' => $loanInterest,
            'amount' => $amount,
            'accountId' => $accountId
        ]);
    }

    #[Route('/loan/create', name: 'loan_create')]
    public function createLoan(SessionInterface $session, AccountRepository $accountRepository): Response
    {
        $loanData = $session->get('loan_data');
        if (!$loanData) {
            $this->addFlash('error', 'Aucune donnée de prêt trouvée.');
            return $this->redirectToRoute('loan_request');
        }

        $user = $this->getUser();
        $account = $accountRepository->findOneBy(['userid' => $user]);

        if (!$account) {
            $this->addFlash('error', 'Aucun compte associé trouvé.');
            return $this->redirectToRoute('loan_request');
        }

        // Créer le prêt
        $this->loanService->createLoan($loanData ['amount'],$user);
        

        // Ajouter le montant au compte de l'utilisateur
        $account->setBalance($account->getBalance() + $loanData['amount']);

        $session->remove('loan_data');

        $this->notificationService->createNotification(
            $user,
            'Votre prêt de ' . $loanData['amount'] . ' $ a été approuvé.'
        );

        $this->addFlash('success', 'Prêt accordé avec succès !');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/loan/repay/{id}', name: 'loan_repay')]
    public function repayLoan(Loan $loan, AccountRepository $accountRepository): Response
    {
        $user = $this->getUser();
        $account = $accountRepository->findOneBy(['user' => $user]);

        if (!$account || $loan->getLoanauthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_dashboard');
        }

        $currentDate = new \DateTime();
        if ($currentDate > $loan->getDuedate()) {
            $this->addFlash('error', 'La date limite de remboursement est dépassée.');
            return $this->redirectToRoute('app_dashboard');
        }

        $totalRepayment = $loan->getAmount() + ($loan->getAmount() * $loan->getInterestRate() / 100);
        if ($account->getBalance() < $totalRepayment) {
            $this->addFlash('error', 'Solde insuffisant pour rembourser le prêt.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Déduire le remboursement et supprimer le prêt
        $account->setBalance($account->getBalance() - $totalRepayment);
        $this->entityManager->remove($loan);
        $this->entityManager->flush();

        $this->notificationService->createNotification(
            $user,
            'Prêt remboursé avec succès. Merci pour votre ponctualité.'
        );

        $this->addFlash('success', 'Prêt remboursé avec succès !');
        return $this->redirectToRoute('app_dashboard');
    }
}
