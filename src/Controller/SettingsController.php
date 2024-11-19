<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\PinType;
use App\Repository\AccountRepository;
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
            throw $this->createNotFoundException('Compte introuvable pour cet utilisateur.');
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
        }

        return $this->render('settings/setPin.html.twig', [
            'pinForm' => $form->createView(),
        ]);
    }

    
}
