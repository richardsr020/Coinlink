<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Form\TransferType;
use App\Service\TransferService;
use App\Service\TransactionHashGeneratorService;
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
    public function transfer(Request $request): Response
    {
        $form = $this->createForm(TransferType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->getUser();
            $fromAccount = $this->em->getRepository(Account::class)->findOneBy(['userid' => $user]);
            $toAccount = $this->em->getRepository(Account::class)->find($data->getToaccountid());
            $amount = $data->getAmount();
            
            if ($toAccount) {

                // Création d'une nouvelle transaction pour le transfert
                $transaction = new Transaction();
                $transaction->setAccountid($fromAccount->getId());
                $transaction->setToAccountid($toAccount->getId());
                $transaction->setAmount($amount);
                $transaction->setTransactiondate(new \DateTimeImmutable());
                $transaction->setDescription('Transfert entre comptes');

                // Génération du hash pour la transaction
                $hash = $this->hashGenerator->generateHash($transaction->getAccountid(), $transaction->getAmount(), $transaction->getTransactiondate(), $transaction->getDescription());
                $transaction->setHash($hash.$transaction->getToaccountid());

                // Appel du service pour traiter le transfert
                $this->transferService->handleTransfer($fromAccount, $toAccount, $amount, $hash);

                // Sauvegarde de la transaction avec le hash dans la base de données
                //$this->em->persist($transaction);
                //$this->em->flush();

                $this->addFlash('success', 'Transfert effectué avec succès.');
                return $this->redirectToRoute('app_dashboard');
            

            } else {
                $this->addFlash('error', 'Ce compte n\'a pas été trouvé.');
                return $this->redirectToRoute('app_transfer');
            }
        }

        return $this->render('transaction/transfer.html.twig', [
            'form' => $form->createView(),

        ]);
    }


}
