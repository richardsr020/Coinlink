<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class WithdrawService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handleWithdraw(Account $account, int $fromAccount, float $amount, string $hash): Transaction
    {
        // Vérifier si le compte a suffisamment de fonds
        if ($account->getBalance() < $amount) {
            throw new \Exception('Solde insuffisant pour le retrait.');
        }

        // Diminuer le solde du compte
        $account->setBalance($account->getBalance() - $amount);

        // Créer une nouvelle transaction
        $transaction = new Transaction();
        $transaction->setToaccountid($account->getId());
        $transaction->setAccountid($fromAccount);
        $transaction->setAmount($amount);
        $transaction->setDescription('Retrait de fonds');
        $transaction->setTransactiondate(new \DateTimeImmutable());
        $transaction->setHash($hash);


        // Persister les changements
        $this->em->persist($transaction);
        $this->em->persist($account);
        $this->em->flush();

        return $transaction;
    }
}
