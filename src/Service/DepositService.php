<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class DepositService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handleDeposit(Account $account, float $amount, string $hash): Transaction
    {
        // Augmenter le solde du compte
        $account->setBalance($account->getBalance() + $amount);

        // Créer une nouvelle transaction
        $transaction = new Transaction();
        $transaction->setAccountid($account->getId());
        $transaction->setToaccountid($account->getId());
        $transaction->setAmount($amount);
        $transaction->setDescription('Dépôt de fonds');
        $transaction->setTransactiondate(new \DateTimeImmutable());
        $transaction->setHash($hash);

        // Persister les changements
        $this->em->persist($transaction);
        $this->em->persist($account);
        $this->em->flush();

        return $transaction;
    }
}
