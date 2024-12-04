<?php
namespace App\Controller;

use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Account;
use App\Service\AccountService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RegistrationFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(
        Request $request, 
        UserPasswordHasherInterface $hasher, 
        EntityManagerInterface $em, 
        UserService $userService,
        MailerService $mailerService,// Injection du service Mailer
        AccountService $accountService
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email est unique
            if (!$userService->isEmailUnique($user->getEmail())) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_registration');
            }
            
            // Encoder le mot de passe
            $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            // Générer le token d'activation
            $activationToken = bin2hex(random_bytes(32));
            $user->setActivationToken($activationToken);
            $user->setEmailVerified(false);
            $user->setHasAcceptedTerms(true);
            $user->setRoles(['ROLE_USER']);
            $user->setLocked(false);
            $user->setCreatedAt(new \DateTimeImmutable());

            // Initialiser le compte
            $account = new Account();
            $account->setAccountnumber($accountService->generateUniqueAccountNumber());
            $account->setBalance($accountService->balanceInit());
            $account->setCreatedat(new \DateTimeImmutable());
            $account->setKycverified(false);
            $user->setAccount($account);

            // Sauvegarder l'utilisateur et son compte
            $em->persist($user);
            $em->flush();

            // Envoyer l'e-mail de vérification
            $subject = 'Confirmez votre email';
            $content = 'NO REPLY Veuillez confirmer votre adresse email en cliquant sur ce lien : ' . 
                        $this->generateUrl('app_verify_email', ['token' => $activationToken], 
                        UrlGeneratorInterface::ABSOLUTE_URL);
            $mailerService->sendEmail($user->getEmail(), $subject, $content);

            // Message flash et redirection vers la page de connexion
            $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vérifier votre e-mail pour activer votre compte.');
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email')]
public function verifyEmail(string $token, UserService $userService, EntityManagerInterface $em): Response
{
    $user = $userService->findByActivationToken($token);

    if (!$user) {
        $this->addFlash('error', 'Le lien d\'activation est invalide.');
        return $this->redirectToRoute('app_registration');
    }

    // Vérifier l'e-mail
    $user->setEmailVerified(true);
    $user->setActivationToken(null); // Supprimer le token d'activation
    $em->persist($user);
    $em->flush();

    $this->addFlash('success', 'Votre e-mail a été vérifié avec succès. Vous pouvez maintenant vous connecter.');
    return $this->redirectToRoute('app_login');
}

#[Route('/email', name: 'test_email')]
public function testEmail(MailerService $mailerService): Response
{
    $mailerService->sendEmail('richardmils02@gmail.com', 'Test de Mail', 'Ceci est un test.');
    return new Response('Email envoyé !');
}


}
