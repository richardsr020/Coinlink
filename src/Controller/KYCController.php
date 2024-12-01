<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\KYCFormType;
use App\Service\CountryService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KYCController extends AbstractController
{
    private CountryService $countryService;
    private NotificationService $notificationService;

    public function __construct(CountryService $countryService, NotificationService $notificationService )
    {
        $this->countryService = $countryService;
        $this->notificationService = $notificationService;
        
    }

    #[Route('/kyc', name: 'app_kyc_form', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Charge l'utilisateur actuel

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour soumettre vos informations KYC.');
        }

        if ($user->getPhone()) {
            // Si le numero exist alor le KYC est deja fait on ne peut plus le refaire, on redirige vers le dashboard
            $this->addFlash('error', 'Impossible de refaire la verification KYC');
            return $this->redirectToRoute('app_dashboard');
        }
        $form = $this->createForm(KYCFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement des fichiers
            $this->handleFileUpload($form, $user);

            // Récupération et formatage du numéro de téléphone
            $data = $form->getData();
            $countryId =$data->getCountry();
            $country = $this->countryService->getAfricanCountry($countryId); // Champ "country" dans le formulaire
         
            $phone = $data->getPhone(); // Champ "phone" dans le formulaire

            try {
                $formattedPhone = $phone; //$this->countryService->formatPhoneNumberWithCountryCode();
                $user->setPhone($formattedPhone);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du formatage du numéro de téléphone : ' . $e->getMessage());
                return $this->redirectToRoute('app_kyc_form');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations KYC ont été soumises avec succès.');
            // Notification de validation du KYC
            $this->notificationService->createNotification(
                $user,
                'KYC soumis un badge apparaitra dans trois jours en cas d\'approbation .'
            );
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('kyc/index.html.twig', [
            'kycForm' => $form->createView(),
        ]);
    }

    private function handleFileUpload($form, User $user): void
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/kyc_thumbnail/';

        foreach (['avatar', 'humanproofimage', 'idcard', 'idcardback', 'residenceproofimage'] as $field) {
            $file = $form->get($field)->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move($uploadDir, $fileName);

                    $setter = 'set' . ucfirst($field);
                    $user->$setter($fileName);
                } catch (FileException $e) {
                    throw new \Exception('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
                }
            }
        }
    }
}
