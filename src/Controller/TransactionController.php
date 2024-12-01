<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\PinType;
use App\Entity\Transaction;
use App\Service\AccountService;
use App\Form\TransferType;
use App\Service\TransferService;
use App\Service\WithdrawService;
use App\Repository\AccountRepository;
use App\Service\TransactionHashGeneratorService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    private TransferService $transferService;
    private TransactionHashGeneratorService $hashGenerator;
    private EntityManagerInterface $em;

    public function __construct(
        TransferService $transferService,
        TransactionHashGeneratorService $hashGenerator, 
        EntityManagerInterface $em
    ) {
        $this->transferService = $transferService;
        $this->hashGenerator = $hashGenerator;
        $this->em = $em;
    }

        #[Route('dashboard/deposite', name: 'app_deposit')]
    public function deposit(Request $request): Response
    {
  
        return $this->render('transaction/deposit.html.twig');
    }
    

        #[Route('dashboard/transfer', name: 'app_transfer')]
    public function transfer(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(TransferType::class);
        $form->handleRequest($request);
        $userAccount = $this->getUser()->getAccount();


        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if($data->getToaccountid() == $userAccount->getId()){
                $this->addFlash('error', 'error', 'Le Transfert vers votre propre compte est impossible');
                return $this->redirectToRoute('app_transfer');
            }

            // Stocke les données dans la session
            $session->set('transfer_data', $data);

            // Ajoute un message flash de confirmation
            $this->addFlash('success', 'Les données de transfert ont été enregistrées avec succès.');

            // Redirige ou traite selon vos besoins
            return $this->redirectToRoute('app_transfer_confirm');
        }

        // Recrée un formulaire vierge si besoin (automatiquement fait après rendu)
        return $this->render('transaction/transfer.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/dashboard/transferConfirm/{confirm?}', name: 'app_transfer_confirm')]
    public function transferConfirm(
        SessionInterface $session, 
        AccountRepository $accountRepository, 
        TransferService $transferService, // Injection du service
        $confirm): Response 
        {
        // Récupère les données de transfert stockées dans la session
        $data = $session->get('transfer_data');

        // Vérifie si les données ont été stockées
        if (!$data) {
            $this->addFlash('error', 'Les identifiants du bénéficiaire ne sont pas renseignés, veuillez les fournir.');
            return $this->redirectToRoute('app_transfer');
        }

        // Récupère le compte cible à partir de l'ID compte dans les données
        $accountId = $data->getToaccountid();
        $targetAccount = $accountRepository->findOneBy(['id' => $accountId]);

        // Vérifie si le compte existe
        if (!$targetAccount) {
            $this->addFlash('error', 'Le compte du bénéficiaire est introuvable.');
            return $this->redirectToRoute('app_transfer');
        }

        // Récupère le montant du transfert depuis les données stockées
        $amount = $data->getAmount();

        // Calcul des frais de transaction via le service
        $fees = $transferService->calculateFees($amount);

        // Récupère les informations utilisateur associées au compte cible
        $user = $targetAccount->getUserid();
        $name = $user->getName();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $fee = $fees;

        // Gère la confirmation ou l'annulation
        if ($confirm === 'true') {
            // Redirige vers la vérification du code PIN pour finaliser la transaction
            return $this->redirectToRoute('app_verifyPin');
        }

        if ($confirm === 'false') {
            $this->addFlash('success', 'La transaction a été annulée avec succès.');
            return $this->redirectToRoute('app_transfer');
        }

        // Rendu de la vue Twig avec les données utilisateur et les frais
        return $this->render('transaction/transfer_confirm.html.twig', [
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'data' => $data, // Données de transfert incluant les frais
            'fees' => $fee, // Frais de transaction
        ]);
    }




        #[Route('/dashboard/verifyPin', name: 'app_verifyPin')]
    public function verifyPin(
        Request $request,
        SessionInterface $session,
        AccountRepository $accountRepository,
        AccountService $accountService
    ): Response 
    {
        // Vérifie si les données de transfert sont disponibles dans la session
        $transferData = $session->get('transfer_data');
        if (!$transferData) {
            $this->addFlash('error', 'Les données de transfert sont introuvables. Veuillez recommencer le processus.');
            return $this->redirectToRoute('app_transfer');
        }

        // Récupère les données nécessaires de la session
        $toAccountId = $transferData->getToaccountid() ?? null;
        $amount = $transferData->getAmount() ?? null;

        if (!$toAccountId || !$amount) {
            $this->addFlash('error', 'Les informations de transfert sont incomplètes.');
            return $this->redirectToRoute('app_transfer');
        }

        // Récupère l'utilisateur actuellement connecté et son compte
        $user = $this->getUser();
        $fromAccount = $accountRepository->findOneBy(['userid' => $user]);

        // Vérifie si le compte source existe
        if (!$fromAccount) {
            $this->addFlash('error', 'Impossible de signer la transaction avec le PIN. Votre compte est introuvable.');
            return $this->redirectToRoute('app_transfer');
        }

        // Récupère le compte bénéficiaire par `id`
        $toAccount = $accountRepository->find($toAccountId);
        if (!$toAccount) {
            $this->addFlash('error', 'Le compte du bénéficiaire est introuvable.');
            return $this->redirectToRoute('app_transfer');
        }

        // Crée et traite le formulaire de validation du PIN
        $form = $this->createForm(PinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le PIN depuis le formulaire
            $data = $form->getData();
            $pin = (int)$data['pin'];

            // Vérifie le hash du compte source en utilisant le service AccountService
            if ($accountService->validateAccountHash($fromAccount, $pin)) {
                try {
                    // Appel du service pour traiter le transfert
                    $hash = $this->hashGenerator->generateHash(
                        $fromAccount->getId(),
                        $amount,
                        new \DateTimeImmutable(),
                        'Transfert entre comptes'
                    );

                    $this->transferService->handleTransfer($fromAccount, $toAccount, $amount, $hash);

                    $this->addFlash('success', 'Transfert effectué avec succès.');
                    return $this->redirectToRoute('app_dashboard');
                } catch (\InvalidArgumentException $e) {
                    // Gérer les fonds insuffisants ou autres erreurs levées
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('app_transfer');
                }
            } else {
                $this->addFlash('error', 'Le code PIN est incorrect.');
            }
        }

        return $this->render('settings/verifyPin.html.twig', [
            'pinForm' => $form->createView(),
        ]);
    }


    /**
     *  méthodes du controller pour la gestion des retraits, etc.
     * ce methode utilisent les meme vues mais avec de legeres modificatrions dans les 
     * au niveau de la logique
     *  */ 

    #[Route('dashboard/withdraw', name: 'app_withdraw')]
    public function withdraw(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(TransferType::class);
        $form->handleRequest($request);
        $userAccount = $this->getUser()->getAccount();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if($data->getToaccountid() == $userAccount->getId()){
                $this->addFlash('error', 'Le retrait vers votre propre compte est impossible.');
                return $this->redirectToRoute('app_withdraw');
            }

            // Stocke les données dans la session
            $session->set('withdraw_data', $data);

            return $this->redirectToRoute('app_withdraw_confirm');
        }

        return $this->render('transaction/withdraw.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/dashboard/withdrawConfirm/{confirm?}', name: 'app_withdraw_confirm')]
    public function withdrawConfirm(
        SessionInterface $session,
        AccountRepository $accountRepository,
        WithdrawService $withdrawService, // Utilisation du service
        string $confirm = null
    ): Response {
        // Récupère les données de transfert depuis la session
        $data = $session->get('withdraw_data');
        if (!$data) {
            $this->addFlash('error', 'Les données de retrait sont introuvables. Veuillez recommencer.');
            return $this->redirectToRoute('app_withdraw');
        }

        // Récupère le compte destinataire
        $toAccount = $accountRepository->find($data->getToaccountid());
        if (!$toAccount) {
            $this->addFlash('error', 'Le compte bénéficiaire est introuvable.');
            return $this->redirectToRoute('app_withdraw');
        }

        // Calcul des frais
        $fees = $withdrawService->calculateWithdrawalFees($data->getAmount());

        if ($confirm === 'true') {
            return $this->redirectToRoute('app_withdraw_verifyPin');
        }

        if ($confirm === 'false') {
            $this->addFlash('success', 'La transaction a été annulée.');
            return $this->redirectToRoute('app_withdraw');
        }

        return $this->render('transaction/withdraw_confirm.html.twig', [
            'name' => $toAccount->getUserid()->getName(),
            'lastname' => $toAccount->getUserid()->getLastname(),
            'email' => $toAccount->getUserid()->getEmail(),
            'data' => $data,
            'fees' => $fees,
        ]);
    }



    
    #[Route('/dashboard/withdraw/verifyPin', name: 'app_withdraw_verifyPin')]
    public function withdraw_verifyPin(
        Request $request,
        SessionInterface $session,
        AccountRepository $accountRepository,
        WithdrawService $withdrawService,
        AccountService $accountService
    ): Response {
        // Récupère les données de transfert
        $transferData = $session->get('withdraw_data');
        if (!$transferData) {
            $this->addFlash('error', 'Les données de retrait sont introuvables. Veuillez recommencer.');
            return $this->redirectToRoute('app_withdraw');
        }

        $toAccount = $accountRepository->find($transferData->getToaccountid());
        $amount = $transferData->getAmount();

        $user = $this->getUser();
        $fromAccount = $accountRepository->findOneBy(['userid' => $user]);
        if (!$fromAccount || !$toAccount) {
            $this->addFlash('error', 'Les comptes spécifiés sont introuvables.');
            return $this->redirectToRoute('app_withdraw');
        }

        $form = $this->createForm(PinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pin = (int)$form->getData()['pin'];

            // Validation du PIN
            if ($accountService->validateAccountHash($fromAccount, $pin)) {
                try {
                    $hash = uniqid('trx_', true);
                    $withdrawService->handleWithdraw($fromAccount, $toAccount, $amount, $hash);

                    $this->addFlash('success', 'Retrait effectué avec succès.');
                    $session->remove('withdraw_data');
                    return $this->redirectToRoute('app_dashboard');
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('app_withdraw');
                }
            } else {
                $this->addFlash('error', 'Le code PIN est incorrect.');
            }
        }

        return $this->render('settings/verifyPin.html.twig', [
            'pinForm' => $form->createView(),
        ]);
    }




}
