<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\Incomes;
use App\Entity\Fee; // Ajout du namespace Fee
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;

class TransferService
{
    private EntityManagerInterface $em;
    private NotificationService $notificationService;
    private FeeService $feeService;

    public function __construct(EntityManagerInterface $em, NotificationService $notificationService, FeeService $feeService)
    {
        $this->em = $em;
        $this->notificationService = $notificationService;
        $this->feeService = $feeService;
    }

    /**
     * Gérer le transfert avec prélèvement des frais.
     */
    public function handleTransfer(Account $fromAccount, Account $toAccount, float $amount, string $hash): Transaction
    {
        // Calculer les frais
        $fees = 0;

        // Vérifier si le compte émetteur a suffisamment de fonds
        if ($fromAccount->getBalance() <= ($amount + $fees)) {
            throw new \InvalidArgumentException('Fonds insuffisants pour effectuer la transaction. Compte tenu de frais');
        }

        // Diminuer le solde du compte émetteur (montant + frais)
        $fromAccount->setBalance($fromAccount->getBalance() - ($amount + $fees));

        // Augmenter le solde du compte récepteur (seulement le montant)
        $toAccount->setBalance($toAccount->getBalance() + $amount);

        // Créer une nouvelle transaction
        $transaction = new Transaction();
        $transaction->setAccountid($fromAccount->getId());
        $transaction->setToAccountid($toAccount->getId());
        $transaction->setAmount($amount);
        $transaction->setDescription('Transfert de fonds avec frais');
        $transaction->setTransactiondate(new \DateTimeImmutable());
        $transaction->setHash($hash);

        // Ajouter les revenus des frais dans la table `Incomes`
        $this->addIncome($fees, $hash);

        // Persister les changements
        $this->em->persist($transaction);
        $this->em->persist($fromAccount);
        $this->em->persist($toAccount);
        $this->em->flush();

        // Envoyer des notifications aux deux utilisateurs
        $this->notificationService->createNotification(
            $fromAccount->getUserid(),
            "Votre compte a été débité de $amount $ pour un transfert de fonds (frais : $fees $)."
        );

        $this->notificationService->createNotification(
            $toAccount->getUserid(),
            "Votre compte a été crédité de $amount $ provenant d'un transfert de fonds."
        );

        return $transaction;
    }

    /**
     * Calculer les frais pour une transaction.
     */
    public function calculateFees(float $amount): float
    {
        return 0;
    }

    /**
     * Ajouter les frais collectés dans la table `Incomes`.
     */
    private function addIncome(float $fees, string $hash): void
    {
        $income = new Incomes();
        $income->setAmount($fees);
        $income->setTransactionhash($hash);
        $income->setCreatedat(new \DateTimeImmutable());

        $this->em->persist($income);
        $this->em->flush();
    }
}
