<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;

class TransferService
{
    private EntityManagerInterface $em;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $em, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->notificationService = $notificationService;
    }

    public function handleTransfer(Account $fromAccount, Account $toAccount, float $amount, string $hash): Transaction
    {
        // Vérifier si le compte émetteur a suffisamment de fonds
        if ($fromAccount->getBalance() < $amount) {
            throw new \Exception('Solde insuffisant pour le transfert.');
        }

        // Diminuer le solde du compte émetteur
        $fromAccount->setBalance($fromAccount->getBalance() - $amount);

        // Augmenter le solde du compte récepteur
        $toAccount->setBalance($toAccount->getBalance() + $amount);

        // Créer une nouvelle transaction
        $transaction = new Transaction();
        $transaction->setAccountid($fromAccount->getId());
        $transaction->setToAccountid($toAccount->getId());
        $transaction->setAmount($amount);
        $transaction->setDescription('Transfert de fonds');
        $transaction->setTransactiondate(new \DateTimeImmutable());
        $transaction->setHash($hash);

        // Persister les changements
        $this->em->persist($transaction);
        $this->em->persist($fromAccount);
        $this->em->persist($toAccount);
        $this->em->flush();

        // Envoyer des notifications aux deux utilisateurs
        $this->notificationService->createNotification(
            $fromAccount->getUserid(),
            "Votre compte a été débité de $amount $ pour un transfert de fonds."
        );

        $this->notificationService->createNotification(
            $toAccount->getUserid(),
            "Votre compte a été crédité de $amount $ provenant d'un transfert de fonds."
        );

        return $transaction;
    }
}
