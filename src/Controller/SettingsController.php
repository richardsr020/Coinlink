<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\PinType;
use App\Form\PasswordType;
use App\Repository\AccountRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings')]
    public function index(): Response
    {
        return $this->render('settings/index.html.twig', [
            'controller_name' => 'SettingsController',
        ]);
    }

    #[Route('/settings/setPin', name: 'app_setPin')]
    public function setPin(Request $request, AccountRepository $accountRepository, AccountService $accountService): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Trouve le compte de l'utilisateur connecté
        $account = $accountRepository->findOneBy(['userid' => $user]);

        // Vérifie si le compte existe
        if (!$account) {
             // Message de confirmation
            $this->addFlash('error', 'Vueillez vous inscrir au prealable.');
             // Redirige vers la route "app_register"
            return $this->redirectToRoute('app_registration');
        }

        // Crée et traite le formulaire
        $form = $this->createForm(PinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire, incluant le PIN
            $data = $form->getData();
            $pin = (int)$data['pin'];

            // Génère et stocke le hash du compte en utilisant le service AccountService
            $accountService->generateAccountHash($account, $pin);

            // Sauvegarde les modifications dans la base de données
           // $accountRepository->save($account, true);

            // Message de confirmation
            $this->addFlash('success', 'Votre code PIN a été défini avec succès.');
             // Redirige vers la route "app_dashboard"
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('settings/setPin.html.twig', [
            'pinForm' => $form->createView(),
        ]);
    }


        #[Route('/settings/resetPin', name: 'app_resetPin')]
    public function resetPin(Request $request, AccountRepository $accountRepository, AccountService $accountService, SessionInterface $session): Response 
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Trouve le compte de l'utilisateur connecté
        $account = $accountRepository->findOneBy(['userid' => $user]);

        // Vérifie si le compte existe
        if (!$account) {
             // Message de confirmation
            $this->addFlash('error', 'Vueillez vous inscrir au prealable.');
             // Redirige vers la route "app_register"
            return $this->redirectToRoute('app_registration');
        }

        // Crée et traite le formulaire
        $form = $this->createForm(PinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire, incluant le PIN
            $data = $form->getData();
            $pin = (int)$data['pin'];

            // Enregistre les données dans la session
            $session->set('reset_pin_data', $pin);

            // Redirige vers la route "app_resetPin_save"
            return $this->redirectToRoute('app_resetPin_save');
        }

        return $this->render('settings/resetPin.html.twig', [
            'pinForm' => $form->createView(),
        ]);
    }


#[Route('/settings/resetPin/save', name: 'app_resetPin_save')]
public function resetPinSave(Request $request, AccountRepository $accountRepository, AccountService $accountService, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response {
    // Récupère l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Trouve le compte de l'utilisateur connecté
    $account = $accountRepository->findOneBy(['userid' => $user]);

    // Vérifie si le compte existe
    if (!$account) {
         // Message de confirmation
            $this->addFlash('error', 'Vueillez vous inscrir au prealable.');
             // Redirige vers la route "app_register"
            return $this->redirectToRoute('app_registration');
    }

    // Récupère les données stockées dans la session
    $pin = $session->get('reset_pin_data');

    if (!$pin) {
        $this->addFlash('error', 'Données de réinitialisation non disponibles.');
        return $this->redirectToRoute('app_resetPin');
    }

    // Crée et traite le formulaire
    $form = $this->createForm(PasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupère le mot de passe saisi dans le formulaire
        $data = $form->getData();
        $inputPassword = $data['password']; // Supposons que le champ dans le formulaire est 'password'

        // Vérifie si le mot de passe saisi correspond au mot de passe stocké
        if (!$passwordHasher->isPasswordValid($user, $inputPassword)) {
            $this->addFlash('error', 'Mot de passe incorrect. Réinitialisation annulée.');

            return $this->redirectToRoute('app_resetPin');
        }

        // Appelle le service pour réinitialiser le hash du compte avec le PIN de la session
        $accountService->resetAccountHash($account, $pin);


        // Supprime les données sensibles de la session
        $session->remove('reset_pin_data');

        // Ajoute un message de confirmation
        $this->addFlash('success', 'Votre code PIN a été réinitialisé avec succès.');

        // Redirige vers une page appropriée après la réinitialisation
        return $this->redirectToRoute('app_settings');
    }

    // Rendu du formulaire
    return $this->render('settings/password.html.twig', [
        'passwordForm' => $form->createView(),
    ]);
}


    
}
